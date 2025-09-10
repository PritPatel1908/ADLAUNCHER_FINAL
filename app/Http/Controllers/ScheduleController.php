<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Schedule;
use App\Models\DeviceLayout;
use App\Models\DeviceScreen;
use Illuminate\Http\Request;
use App\Models\ScheduleMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessVideoUpload;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('schedule.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check if this is a JSON request (from chunked upload)
        if ($request->isJson()) {
            return $this->storeWithChunkedUpload($request);
        }

        // Debug logging
        Log::info('Schedule creation request received', [
            'all_data' => $request->all(),
            'files' => $request->allFiles(),
            'has_schedule_name' => $request->has('schedule_name'),
            'has_device_id' => $request->has('device_id'),
            'has_start_date' => $request->has('schedule_start_date_time'),
            'has_end_date' => $request->has('schedule_end_date_time'),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
        ]);

        try {
            $request->validate([
                'schedule_name' => 'required|string|max:255',
                'device_id' => 'required|exists:devices,id',
                'layout_id' => 'nullable|exists:device_layouts,id',
                'schedule_start_date_time' => 'nullable|date',
                'schedule_end_date_time' => 'nullable|date',
                'media_title.*' => 'nullable|string|max:255',
                'media_type.*' => 'nullable|string|in:image,video,audio,mp4,png,jpg,pdf',
                'media_file.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,avi,mov,mp3,wav,pdf|max:204800', // 200MB limit
                'media_screen_id.*' => 'nullable|exists:device_screens,id',
                'media_duration_seconds.*' => 'nullable|integer|min:1|max:86400',
                'media_start_date_time.*' => 'nullable|date',
                'media_end_date_time.*' => 'nullable|date',
                'media_play_forever.*' => 'nullable|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Schedule validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
                'files' => $request->allFiles()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $schedule = Schedule::create([
                'schedule_name' => $request->schedule_name,
                'device_id' => $request->device_id,
                'layout_id' => $request->layout_id,
            ]);

            // Parse schedule-level defaults
            $scheduleLevelStart = null;
            $scheduleLevelEnd = null;
            if (!empty($request->schedule_start_date_time)) {
                try {
                    $scheduleLevelStart = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->schedule_start_date_time);
                } catch (\Exception $e) {
                    try {
                        $scheduleLevelStart = \Carbon\Carbon::parse($request->schedule_start_date_time);
                    } catch (\Exception $e2) {
                        $scheduleLevelStart = null;
                    }
                }
            }
            if (!empty($request->schedule_end_date_time)) {
                try {
                    $scheduleLevelEnd = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->schedule_end_date_time);
                } catch (\Exception $e) {
                    try {
                        $scheduleLevelEnd = \Carbon\Carbon::parse($request->schedule_end_date_time);
                    } catch (\Exception $e2) {
                        $scheduleLevelEnd = null;
                    }
                }
            }
            $scheduleLevelForever = false; // Default to false since schedule-level play_forever is removed

            // Handle ScheduleMedia creation
            if ($request->has('media_title')) {
                $mediaTitles = $request->input('media_title', []);
                $mediaTypes = $request->input('media_type', []);
                $mediaFiles = $request->file('media_file', []);
                $mediaScreenIds = $request->input('media_screen_id', []);
                $mediaDurations = $request->input('media_duration_seconds', []);
                $mediaStarts = $request->input('media_start_date_time', []);
                $mediaEnds = $request->input('media_end_date_time', []);
                $mediaForever = $request->input('media_play_forever', []);

                for ($i = 0; $i < count($mediaTitles); $i++) {
                    // Create media if any of title, type, or file is provided
                    if (!empty($mediaTitles[$i]) || !empty($mediaTypes[$i]) || (isset($mediaFiles[$i]) && $mediaFiles[$i]->isValid())) {
                        $startAt = null;
                        $endAt = null;
                        if (!empty($mediaStarts[$i])) {
                            try {
                                $startAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaStarts[$i]);
                            } catch (\Exception $e) {
                                $startAt = null;
                            }
                        }
                        if (!empty($mediaEnds[$i])) {
                            try {
                                $endAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaEnds[$i]);
                            } catch (\Exception $e) {
                                $endAt = null;
                            }
                        }

                        // Fallbacks to schedule-level values when per-media ones are absent
                        if ($startAt === null) {
                            $startAt = $scheduleLevelStart;
                        }
                        if ($endAt === null) {
                            $endAt = $scheduleLevelEnd;
                        }
                        $playForeverVal = isset($mediaForever[$i]) && $mediaForever[$i] !== null && $mediaForever[$i] !== ''
                            ? (!empty($mediaForever[$i]) ? true : false)
                            : $scheduleLevelForever;

                        // Overlap validation: only when screen and both dates are present
                        if (!empty($mediaScreenIds[$i]) && $startAt !== null && $endAt !== null) {
                            if ($this->screenHasOverlap($mediaScreenIds[$i], $startAt, $endAt, null, $schedule->id)) {
                                DB::rollBack();
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Validation failed',
                                    'errors' => [
                                        'overlap' => ['Selected time range overlaps with existing media on this screen.']
                                    ]
                                ], 422);
                            }
                        }

                        $mediaData = [
                            'schedule_id' => $schedule->id,
                            'title' => $mediaTitles[$i] ?? null,
                            'media_type' => $mediaTypes[$i] ?? null,
                            'screen_id' => $mediaScreenIds[$i] ?? null,
                            'duration_seconds' => $mediaDurations[$i] ?? null,
                            'schedule_start_date_time' => $startAt,
                            'schedule_end_date_time' => $endAt,
                            'play_forever' => $playForeverVal,
                        ];

                        // Handle file upload
                        if (isset($mediaFiles[$i]) && $mediaFiles[$i]->isValid()) {
                            $file = $mediaFiles[$i];
                            $filename = time() . '_' . $file->getClientOriginalName();
                            $path = $file->storeAs('schedule_media', $filename, 'public');
                            $mediaData['media_file'] = $path;
                        } elseif (isset($mediaFiles[$i])) {
                            // Log file upload errors for debugging
                            Log::error('File upload failed', [
                                'file_name' => $mediaFiles[$i]->getClientOriginalName(),
                                'file_size' => $mediaFiles[$i]->getSize(),
                                'file_mime' => $mediaFiles[$i]->getMimeType(),
                                'errors' => $mediaFiles[$i]->getError(),
                                'error_message' => $mediaFiles[$i]->getErrorMessage()
                            ]);
                        }

                        ScheduleMedia::create($mediaData);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Schedule created successfully',
                'schedule' => $schedule->load(['device', 'layout', 'medias.screen'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'files' => $request->allFiles()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        $schedule->load(['device', 'layout', 'medias.screen']);

        // Check if request is AJAX
        if (request()->ajax()) {
            return response()->json([
                'schedule' => $schedule
            ]);
        }

        return view('schedule.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule)
    {
        $schedule->load(['device', 'layout', 'medias.screen']);

        // Add ISO-local formatted datetime strings for inputs
        if ($schedule->medias) {
            foreach ($schedule->medias as $media) {
                $media->schedule_start_date_time_formatted = $media->schedule_start_date_time
                    ? $media->schedule_start_date_time->format('Y-m-d\TH:i')
                    : null;
                $media->schedule_end_date_time_formatted = $media->schedule_end_date_time
                    ? $media->schedule_end_date_time->format('Y-m-d\TH:i')
                    : null;
            }
        }

        return response()->json([
            'success' => true,
            'schedule' => $schedule
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        // Check if this is a JSON request (from chunked upload)
        if ($request->isJson()) {
            return $this->updateWithChunkedUpload($request, $schedule);
        }

        $request->validate([
            'schedule_name' => 'required|string|max:255',
            'device_id' => 'required|exists:devices,id',
            'layout_id' => 'nullable|exists:device_layouts,id',
            'schedule_start_date_time' => 'nullable|date',
            'schedule_end_date_time' => 'nullable|date',
            'edit_media_title.*' => 'nullable|string|max:255',
            'edit_media_type.*' => 'nullable|string|in:image,video,audio,mp4,png,jpg,pdf',
            'edit_media_file.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,avi,mov,mp3,wav,pdf|max:204800', // 200MB limit
            'edit_media_screen_id.*' => 'nullable|exists:device_screens,id',
            'edit_media_duration_seconds.*' => 'nullable|integer|min:1|max:86400',
            'edit_media_start_date_time.*' => 'nullable|date',
            'edit_media_end_date_time.*' => 'nullable|date',
            'edit_media_play_forever.*' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $schedule->update([
                'schedule_name' => $request->schedule_name,
                'device_id' => $request->device_id,
                'layout_id' => $request->layout_id,
            ]);

            // Parse schedule-level defaults
            $scheduleLevelStart = null;
            $scheduleLevelEnd = null;
            if (!empty($request->schedule_start_date_time)) {
                try {
                    $scheduleLevelStart = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->schedule_start_date_time);
                } catch (\Exception $e) {
                    try {
                        $scheduleLevelStart = \Carbon\Carbon::parse($request->schedule_start_date_time);
                    } catch (\Exception $e2) {
                        $scheduleLevelStart = null;
                    }
                }
            }
            if (!empty($request->schedule_end_date_time)) {
                try {
                    $scheduleLevelEnd = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $request->schedule_end_date_time);
                } catch (\Exception $e) {
                    try {
                        $scheduleLevelEnd = \Carbon\Carbon::parse($request->schedule_end_date_time);
                    } catch (\Exception $e2) {
                        $scheduleLevelEnd = null;
                    }
                }
            }
            $scheduleLevelForever = false; // Default to false since schedule-level play_forever is removed

            // Handle ScheduleMedia updates
            if ($request->has('edit_media_title')) {
                $mediaTitles = $request->input('edit_media_title', []);
                $mediaTypes = $request->input('edit_media_type', []);
                $mediaFiles = $request->file('edit_media_file', []);
                $mediaIds = $request->input('edit_media_id', []);
                $mediaScreenIds = $request->input('edit_media_screen_id', []);
                $mediaDurations = $request->input('edit_media_duration_seconds', []);
                $mediaStarts = $request->input('edit_media_start_date_time', []);
                $mediaEnds = $request->input('edit_media_end_date_time', []);
                $mediaForever = $request->input('edit_media_play_forever', []);

                for ($i = 0; $i < count($mediaTitles); $i++) {
                    if (!empty($mediaTitles[$i]) || !empty($mediaTypes[$i])) {
                        $startAt = null;
                        $endAt = null;
                        if (!empty($mediaStarts[$i])) {
                            try {
                                $startAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaStarts[$i]);
                            } catch (\Exception $e) {
                                $startAt = null;
                            }
                        }
                        if (!empty($mediaEnds[$i])) {
                            try {
                                $endAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaEnds[$i]);
                            } catch (\Exception $e) {
                                $endAt = null;
                            }
                        }

                        // Fallbacks to schedule-level values when per-media ones are absent
                        if ($startAt === null) {
                            $startAt = $scheduleLevelStart;
                        }
                        if ($endAt === null) {
                            $endAt = $scheduleLevelEnd;
                        }
                        $playForeverVal = isset($mediaForever[$i]) && $mediaForever[$i] !== null && $mediaForever[$i] !== ''
                            ? (!empty($mediaForever[$i]) ? true : false)
                            : $scheduleLevelForever;

                        // Overlap validation: only when screen and both dates are present
                        if (!empty($mediaScreenIds[$i]) && $startAt !== null && $endAt !== null) {
                            $excludeId = !empty($mediaIds[$i]) ? $mediaIds[$i] : null;
                            if ($this->screenHasOverlap($mediaScreenIds[$i], $startAt, $endAt, $excludeId, $schedule->id)) {
                                DB::rollBack();
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Validation failed',
                                    'errors' => [
                                        'overlap' => ['Selected time range overlaps with existing media on this screen.']
                                    ]
                                ], 422);
                            }
                        }

                        $mediaData = [
                            'title' => $mediaTitles[$i] ?? null,
                            'media_type' => $mediaTypes[$i] ?? null,
                            'screen_id' => $mediaScreenIds[$i] ?? null,
                            'duration_seconds' => $mediaDurations[$i] ?? null,
                            'schedule_start_date_time' => $startAt,
                            'schedule_end_date_time' => $endAt,
                            'play_forever' => $playForeverVal,
                        ];

                        // Handle file upload
                        if (isset($mediaFiles[$i]) && $mediaFiles[$i]->isValid()) {
                            $file = $mediaFiles[$i];
                            $filename = time() . '_' . $file->getClientOriginalName();
                            $path = $file->storeAs('schedule_media', $filename, 'public');
                            $mediaData['media_file'] = $path;
                        } elseif (isset($mediaFiles[$i])) {
                            // Log file upload errors for debugging
                            Log::error('File upload failed in update', [
                                'file_name' => $mediaFiles[$i]->getClientOriginalName(),
                                'file_size' => $mediaFiles[$i]->getSize(),
                                'file_mime' => $mediaFiles[$i]->getMimeType(),
                                'errors' => $mediaFiles[$i]->getError(),
                                'error_message' => $mediaFiles[$i]->getErrorMessage()
                            ]);
                        }

                        // Update existing media or create new one
                        if (!empty($mediaIds[$i])) {
                            $media = ScheduleMedia::find($mediaIds[$i]);
                            if ($media) {
                                $updateData = [
                                    'title' => $mediaData['title'] ?? null,
                                    'media_type' => $mediaData['media_type'] ?? null,
                                    'screen_id' => $mediaData['screen_id'] ?? null,
                                    'duration_seconds' => $mediaData['duration_seconds'] ?? null,
                                    'play_forever' => $mediaData['play_forever'] ?? false,
                                ];

                                // Add file path if provided
                                if (isset($mediaData['media_file']) && !empty($mediaData['media_file'])) {
                                    $updateData['media_file'] = $mediaData['media_file'];
                                }

                                // Handle date times
                                if (isset($mediaData['start_date_time']) && !empty($mediaData['start_date_time'])) {
                                    try {
                                        $updateData['schedule_start_date_time'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaData['start_date_time']);
                                    } catch (\Exception $e) {
                                        $updateData['schedule_start_date_time'] = null;
                                    }
                                }

                                if (isset($mediaData['end_date_time']) && !empty($mediaData['end_date_time'])) {
                                    try {
                                        $updateData['schedule_end_date_time'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaData['end_date_time']);
                                    } catch (\Exception $e) {
                                        $updateData['schedule_end_date_time'] = null;
                                    }
                                }

                                // Overlap validation: only when screen and both dates are present
                                if (!empty($updateData['screen_id']) && !empty($updateData['schedule_start_date_time']) && !empty($updateData['schedule_end_date_time'])) {
                                    if ($this->screenHasOverlap($updateData['screen_id'], $updateData['schedule_start_date_time'], $updateData['schedule_end_date_time'], $media->id, $schedule->id)) {
                                        DB::rollBack();
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Validation failed',
                                            'errors' => [
                                                'overlap' => ['Selected time range overlaps with existing media on this screen.']
                                            ]
                                        ], 422);
                                    }
                                }

                                $media->update($updateData);
                            }
                        } else {
                            $mediaData['schedule_id'] = $schedule->id;
                            ScheduleMedia::create($mediaData);
                        }
                    }
                }
            }

            // Handle media deletions
            if ($request->has('delete_media_ids')) {
                $deleteIds = $request->input('delete_media_ids', []);
                ScheduleMedia::whereIn('id', $deleteIds)->delete();
            }

            DB::commit();

            // Load the schedule with relationships
            $schedule->load(['device', 'layout', 'medias.screen']);

            // Add formatted dates from first media to match the blade template format
            $firstMedia = $schedule->medias->first();
            $schedule->formatted_start_date = $firstMedia && $firstMedia->schedule_start_date_time ? \Carbon\Carbon::parse($firstMedia->schedule_start_date_time)->format('d M Y, h:i A') : null;
            $schedule->formatted_end_date = $firstMedia && $firstMedia->schedule_end_date_time ? \Carbon\Carbon::parse($firstMedia->schedule_end_date_time)->format('d M Y, h:i A') : null;
            $schedule->formatted_created_date = $schedule->created_at->format('d M Y, h:i A');

            // Format media created dates
            if ($schedule->medias) {
                foreach ($schedule->medias as $media) {
                    $media->formatted_created_date = $media->created_at ? $media->created_at->format('d M Y, h:i A') : 'N/A';
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule update error: ' . $e->getMessage(), [
                'schedule_id' => $schedule->id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        try {
            $schedule->delete();
            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get devices data for DataTables
     */
    public function getData(Request $request)
    {
        // Build a base query WITHOUT grouping to stay compatible with SQL Server
        $baseQuery = Schedule::with(['device', 'layout', 'medias.screen'])
            ->select('schedules.*');

        // Filters
        if ($request->filled('name_filter')) {
            $baseQuery->where('schedule_name', 'like', '%' . $request->name_filter . '%');
        }
        if ($request->filled('device_filter')) {
            $baseQuery->where('device_id', $request->device_filter);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            // Filter schedules that have at least one media in the date range
            $start = $request->start_date;
            $end = $request->end_date . ' 23:59:59';
            $baseQuery->whereExists(function ($q) use ($start, $end) {
                $q->from('schedule_medias as sm')
                    ->whereColumn('sm.schedule_id', 'schedules.id')
                    ->whereBetween('sm.schedule_start_date_time', [$start, $end]);
            });
        }

        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);

        // Count distinct schedules to avoid over-counting due to the join
        $recordsTotal = (clone $baseQuery)->distinct('schedules.id')->count('schedules.id');

        $searchValue = $request->input('search.value');
        if ($searchValue) {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('schedule_name', 'like', "%$searchValue%");
            });
        }

        $recordsFiltered = (clone $baseQuery)->distinct('schedules.id')->count('schedules.id');

        // For sorting by media start time, join a subquery that aggregates per-schedule
        $aggSub = DB::table('schedule_medias')
            ->select('schedule_id', DB::raw('MAX(schedule_start_date_time) as latest_start'))
            ->groupBy('schedule_id');

        $dataQuery = (clone $baseQuery)
            ->leftJoinSub($aggSub, 'smagg', function ($join) {
                $join->on('schedules.id', '=', 'smagg.schedule_id');
            });

        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'newest':
                    $dataQuery->orderBy('smagg.latest_start', 'desc');
                    break;
                case 'oldest':
                    $dataQuery->orderBy('smagg.latest_start', 'asc');
                    break;
                case 'name_asc':
                    $dataQuery->orderBy('schedule_name', 'asc');
                    break;
                case 'name_desc':
                    $dataQuery->orderBy('schedule_name', 'desc');
                    break;
                default:
                    $dataQuery->orderBy('smagg.latest_start', 'desc');
            }
        } else {
            $dataQuery->orderBy('smagg.latest_start', 'desc');
        }

        $schedules = $dataQuery
            ->skip($start)
            ->take($length)
            ->get();

        $data = $schedules->map(function ($s) {
            return [
                'id' => $s->id,
                'schedule_name' => $s->schedule_name,
                'device' => $s->device ? ($s->device->name ?? $s->device->unique_id) : null,
                'layout' => $s->layout ? $s->layout->layout_name : null,
                'created_at' => $s->created_at,
                'updated_at' => $s->updated_at,
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created device layout.
     */
    public function storeLayout(Request $request)
    {
        $request->validate([
            'layout_name' => 'required|string|max:255',
            'layout_type' => 'required|integer|in:0,1,2,3',
            'device_id' => 'required|exists:devices,id',
            'status' => 'required|integer|in:0,1,2,3',
        ]);

        try {
            $deviceLayout = DeviceLayout::create([
                'layout_name' => $request->layout_name,
                'layout_type' => $request->layout_type,
                'device_id' => $request->device_id,
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device layout created successfully',
                'layout' => $deviceLayout
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create device layout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified device layout.
     */
    public function updateLayout(Request $request, DeviceLayout $deviceLayout)
    {
        $request->validate([
            'layout_name' => 'required|string|max:255',
            'layout_type' => 'required|integer|in:0,1,2,3',
            'status' => 'required|integer|in:0,1,2,3',
        ]);

        try {
            $deviceLayout->update([
                'layout_name' => $request->layout_name,
                'layout_type' => $request->layout_type,
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device layout updated successfully',
                'layout' => $deviceLayout
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update device layout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified device layout.
     */
    public function destroyLayout(DeviceLayout $deviceLayout)
    {
        try {
            $deviceLayout->delete();
            return response()->json([
                'success' => true,
                'message' => 'Device layout deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete device layout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all device layouts.
     */
    public function getLayouts()
    {
        try {
            $layouts = DeviceLayout::with('device')->get();
            return response()->json([
                'success' => true,
                'layouts' => $layouts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load device layouts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle chunked file upload for large video files
     */
    public function chunkedUpload(Request $request)
    {
        try {
            $action = $request->input('action');

            switch ($action) {
                case 'initialize':
                    return $this->initializeChunkedUpload($request);
                case 'upload_chunk':
                    return $this->uploadChunk($request);
                case 'finalize':
                    return $this->finalizeChunkedUpload($request);
                case 'single_upload':
                    return $this->handleSingleUpload($request);
                case 'check_status':
                    return $this->checkProcessingStatus($request);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action'
                    ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Chunked upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize chunked upload session
     */
    private function initializeChunkedUpload(Request $request)
    {
        $fileId = $request->input('file_id');
        $fileName = $request->input('file_name');
        $fileSize = $request->input('file_size');
        $fileType = $request->input('file_type');
        $totalChunks = $request->input('total_chunks');
        $chunkSize = $request->input('chunk_size');

        // Validate file type
        $allowedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/webm', 'video/quicktime'];
        if (!in_array($fileType, $allowedTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file type. Only video files are allowed.'
            ], 400);
        }

        // Validate file size (max 2GB)
        if ($fileSize > 2 * 1024 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'message' => 'File too large. Maximum size is 2GB.'
            ], 400);
        }

        // Store upload session info in cache
        $sessionData = [
            'file_id' => $fileId,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'file_type' => $fileType,
            'total_chunks' => $totalChunks,
            'chunk_size' => $chunkSize,
            'uploaded_chunks' => [],
            'created_at' => now(),
            'expires_at' => now()->addHours(2) // 2 hour expiry
        ];

        Cache::put("chunked_upload_{$fileId}", $sessionData, 7200); // 2 hours

        return response()->json([
            'success' => true,
            'message' => 'Upload session initialized',
            'file_id' => $fileId
        ]);
    }

    /**
     * Upload a single chunk
     */
    private function uploadChunk(Request $request)
    {
        $fileId = $request->input('file_id');
        $chunkIndex = $request->input('chunk_index');
        $chunk = $request->file('chunk');

        if (!$chunk || !$chunk->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid chunk file'
            ], 400);
        }

        // Get session data
        $sessionData = Cache::get("chunked_upload_{$fileId}");
        if (!$sessionData) {
            return response()->json([
                'success' => false,
                'message' => 'Upload session not found or expired'
            ], 404);
        }

        // Store chunk
        $chunkPath = "chunks/{$fileId}/chunk_{$chunkIndex}";
        Storage::disk('local')->put($chunkPath, file_get_contents($chunk->getRealPath()));

        // Update session data
        $sessionData['uploaded_chunks'][] = $chunkIndex;
        Cache::put("chunked_upload_{$fileId}", $sessionData, 7200);

        return response()->json([
            'success' => true,
            'message' => 'Chunk uploaded successfully',
            'chunk_index' => $chunkIndex,
            'uploaded_chunks' => count($sessionData['uploaded_chunks']),
            'total_chunks' => $sessionData['total_chunks']
        ]);
    }

    /**
     * Finalize chunked upload and combine chunks
     */
    private function finalizeChunkedUpload(Request $request)
    {
        $fileId = $request->input('file_id');

        // Get session data
        $sessionData = Cache::get("chunked_upload_{$fileId}");
        if (!$sessionData) {
            return response()->json([
                'success' => false,
                'message' => 'Upload session not found or expired'
            ], 404);
        }

        // Verify all chunks are uploaded
        $expectedChunks = range(0, $sessionData['total_chunks'] - 1);
        $uploadedChunks = $sessionData['uploaded_chunks'];

        if (count($uploadedChunks) !== $sessionData['total_chunks']) {
            return response()->json([
                'success' => false,
                'message' => 'Not all chunks uploaded. Missing: ' . implode(',', array_diff($expectedChunks, $uploadedChunks))
            ], 400);
        }

        try {
            // For large video files, dispatch background job for processing
            if ($sessionData['file_size'] > 100 * 1024 * 1024 && str_starts_with($sessionData['file_type'], 'video/')) {
                // Dispatch background job
                ProcessVideoUpload::dispatch(
                    $fileId,
                    $sessionData['file_name'],
                    $sessionData['file_size'],
                    $sessionData['file_type'],
                    $sessionData['total_chunks'],
                    $sessionData['chunk_size']
                );

                return response()->json([
                    'success' => true,
                    'message' => 'File upload completed. Processing in background...',
                    'file_id' => $fileId,
                    'processing' => true,
                    'file_size' => $sessionData['file_size']
                ]);
            } else {
                // For smaller files, process immediately
                $finalFileName = time() . '_' . $sessionData['file_name'];
                $finalPath = "schedule_media/{$finalFileName}";

                $finalFile = Storage::disk('public')->path($finalPath);
                $finalFileHandle = fopen($finalFile, 'wb');

                for ($i = 0; $i < $sessionData['total_chunks']; $i++) {
                    $chunkPath = "chunks/{$fileId}/chunk_{$i}";
                    $chunkContent = Storage::disk('local')->get($chunkPath);
                    fwrite($finalFileHandle, $chunkContent);
                }

                fclose($finalFileHandle);

                // Clean up chunks
                Storage::disk('local')->deleteDirectory("chunks/{$fileId}");

                // Clean up session
                Cache::forget("chunked_upload_{$fileId}");

                return response()->json([
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'file_path' => $finalPath,
                    'file_name' => $finalFileName,
                    'file_size' => $sessionData['file_size']
                ]);
            }
        } catch (\Exception $e) {
            // Clean up on error
            Storage::disk('local')->deleteDirectory("chunks/{$fileId}");
            Cache::forget("chunked_upload_{$fileId}");

            throw $e;
        }
    }

    /**
     * Handle single file upload (for smaller files)
     */
    private function handleSingleUpload(Request $request)
    {
        $file = $request->file('file');

        if (!$file || !$file->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file'
            ], 400);
        }

        // Validate file type
        $allowedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/webm', 'video/quicktime', 'image/jpeg', 'image/png', 'image/gif', 'audio/mpeg', 'audio/wav', 'application/pdf'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file type'
            ], 400);
        }

        // Validate file size (max 200MB for single upload)
        if ($file->getSize() > 200 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'message' => 'File too large for single upload. Use chunked upload for files larger than 200MB.'
            ], 400);
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('schedule_media', $filename, 'public');

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'file_path' => $path,
            'file_name' => $filename,
            'file_size' => $file->getSize()
        ]);
    }

    /**
     * Check processing status of uploaded file
     */
    private function checkProcessingStatus(Request $request)
    {
        $fileId = $request->input('file_id');

        // Get session data
        $sessionData = Cache::get("chunked_upload_{$fileId}");
        if (!$sessionData) {
            return response()->json([
                'success' => false,
                'message' => 'Upload session not found or expired'
            ], 404);
        }

        if (isset($sessionData['processing_completed']) && $sessionData['processing_completed']) {
            return response()->json([
                'success' => true,
                'message' => 'Processing completed',
                'file_path' => $sessionData['final_file_path'],
                'file_name' => $sessionData['final_file_name'],
                'file_size' => $sessionData['file_size'],
                'completed' => true
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Processing in progress...',
                'file_id' => $fileId,
                'completed' => false
            ]);
        }
    }

    /**
     * Update schedule with chunked upload data
     */
    private function updateWithChunkedUpload(Request $request, Schedule $schedule)
    {
        try {
            $data = $request->json()->all();

            Log::info('Update with chunked upload - JSON data received', [
                'schedule_id' => $schedule->id,
                'data' => $data,
                'content_type' => $request->header('Content-Type'),
                'has_medias' => isset($data['medias']),
                'medias_count' => isset($data['medias']) ? count($data['medias']) : 0
            ]);

            // Validate the JSON data
            $validator = Validator::make($data, [
                'schedule_name' => 'required|string|max:255',
                'device_id' => 'required|exists:devices,id',
                'layout_id' => 'nullable|exists:device_layouts,id',
                'schedule_start_date_time' => 'nullable|date',
                'schedule_end_date_time' => 'nullable|date',
                'play_forever' => 'nullable|boolean',
                'medias' => 'nullable|array',
                'medias.*.id' => 'nullable|exists:schedule_media,id',
                'medias.*.title' => 'nullable|string|max:255',
                'medias.*.media_type' => 'nullable|string|in:image,video,audio,mp4,png,jpg,pdf',
                'medias.*.screen_id' => 'nullable|exists:device_screens,id',
                'medias.*.duration_seconds' => 'nullable|integer|min:1|max:86400',
                'medias.*.start_date_time' => 'nullable|date',
                'medias.*.end_date_time' => 'nullable|date',
                'medias.*.play_forever' => 'nullable|boolean',
                'medias.*.media_file' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update schedule basic info
            $schedule->update([
                'schedule_name' => $data['schedule_name'],
                'device_id' => $data['device_id'],
                'layout_id' => $data['layout_id'] ?? null,
            ]);

            // Handle media updates
            if (isset($data['medias']) && is_array($data['medias'])) {
                foreach ($data['medias'] as $mediaData) {
                    if (isset($mediaData['id']) && !empty($mediaData['id'])) {
                        // Update existing media
                        $media = ScheduleMedia::find($mediaData['id']);
                        if ($media) {
                            $updateData = [
                                'title' => $mediaData['title'] ?? null,
                                'media_type' => $mediaData['media_type'] ?? null,
                                'screen_id' => $mediaData['screen_id'] ?? null,
                                'duration_seconds' => $mediaData['duration_seconds'] ?? null,
                                'play_forever' => $mediaData['play_forever'] ?? false,
                            ];

                            // Add file path if provided
                            if (isset($mediaData['media_file']) && !empty($mediaData['media_file'])) {
                                $updateData['media_file'] = $mediaData['media_file'];
                            }

                            // Handle date times
                            if (isset($mediaData['start_date_time']) && !empty($mediaData['start_date_time'])) {
                                try {
                                    $updateData['schedule_start_date_time'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaData['start_date_time']);
                                } catch (\Exception $e) {
                                    $updateData['schedule_start_date_time'] = null;
                                }
                            }

                            if (isset($mediaData['end_date_time']) && !empty($mediaData['end_date_time'])) {
                                try {
                                    $updateData['schedule_end_date_time'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaData['end_date_time']);
                                } catch (\Exception $e) {
                                    $updateData['schedule_end_date_time'] = null;
                                }
                            }

                            // Overlap validation: only when screen and both dates are present
                            if (!empty($updateData['screen_id']) && !empty($updateData['schedule_start_date_time']) && !empty($updateData['schedule_end_date_time'])) {
                                if ($this->screenHasOverlap($updateData['screen_id'], $updateData['schedule_start_date_time'], $updateData['schedule_end_date_time'], $media->id, $schedule->id)) {
                                    DB::rollBack();
                                    return response()->json([
                                        'success' => false,
                                        'message' => 'Validation failed',
                                        'errors' => [
                                            'overlap' => ['Selected time range overlaps with existing media on this screen.']
                                        ]
                                    ], 422);
                                }
                            }

                            $media->update($updateData);
                        }
                    } else {
                        // Create new media
                        $createData = [
                            'schedule_id' => $schedule->id,
                            'title' => $mediaData['title'] ?? null,
                            'media_type' => $mediaData['media_type'] ?? null,
                            'screen_id' => $mediaData['screen_id'] ?? null,
                            'duration_seconds' => $mediaData['duration_seconds'] ?? null,
                            'play_forever' => $mediaData['play_forever'] ?? false,
                        ];

                        // Add file path if provided
                        if (isset($mediaData['media_file']) && !empty($mediaData['media_file'])) {
                            $createData['media_file'] = $mediaData['media_file'];
                        }

                        // Handle date times
                        if (isset($mediaData['start_date_time']) && !empty($mediaData['start_date_time'])) {
                            try {
                                $createData['schedule_start_date_time'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaData['start_date_time']);
                            } catch (\Exception $e) {
                                $createData['schedule_start_date_time'] = null;
                            }
                        }

                        if (isset($mediaData['end_date_time']) && !empty($mediaData['end_date_time'])) {
                            try {
                                $createData['schedule_end_date_time'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaData['end_date_time']);
                            } catch (\Exception $e) {
                                $createData['schedule_end_date_time'] = null;
                            }
                        }

                        // Overlap validation: only when screen and both dates are present
                        if (!empty($createData['screen_id']) && !empty($createData['schedule_start_date_time']) && !empty($createData['schedule_end_date_time'])) {
                            if ($this->screenHasOverlap($createData['screen_id'], $createData['schedule_start_date_time'], $createData['schedule_end_date_time'], null, $schedule->id)) {
                                DB::rollBack();
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Validation failed',
                                    'errors' => [
                                        'overlap' => ['Selected time range overlaps with existing media on this screen.']
                                    ]
                                ], 422);
                            }
                        }

                        ScheduleMedia::create($createData);
                    }
                }
            }

            DB::commit();

            // Load updated schedule with relationships
            $updatedSchedule = Schedule::with(['device', 'layout', 'medias.screen'])->find($schedule->id);

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'schedule' => $updatedSchedule
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule update with chunked upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'schedule_id' => $schedule->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store schedule with chunked upload data
     */
    private function storeWithChunkedUpload(Request $request)
    {
        try {
            $data = $request->json()->all();

            Log::info('Store with chunked upload - JSON data received', [
                'data' => $data,
                'content_type' => $request->header('Content-Type'),
                'has_medias' => isset($data['medias']),
                'medias_count' => isset($data['medias']) ? count($data['medias']) : 0
            ]);

            // Validate the JSON data
            $validator = Validator::make($data, [
                'schedule_name' => 'required|string|max:255',
                'device_id' => 'required|exists:devices,id',
                'layout_id' => 'nullable|exists:device_layouts,id',
                'schedule_start_date_time' => 'nullable|date',
                'schedule_end_date_time' => 'nullable|date',
                'play_forever' => 'nullable|boolean',
                'medias' => 'nullable|array',
                'medias.*.title' => 'nullable|string|max:255',
                'medias.*.media_type' => 'nullable|string|in:image,video,audio,mp4,png,jpg,pdf',
                'medias.*.screen_id' => 'nullable|exists:device_screens,id',
                'medias.*.duration_seconds' => 'nullable|integer|min:1|max:86400',
                'medias.*.start_date_time' => 'nullable|date',
                'medias.*.end_date_time' => 'nullable|date',
                'medias.*.play_forever' => 'nullable|boolean',
                'medias.*.media_file' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create schedule
            $schedule = Schedule::create([
                'schedule_name' => $data['schedule_name'],
                'device_id' => $data['device_id'],
                'layout_id' => $data['layout_id'] ?? null,
            ]);

            // Handle media creation
            if (isset($data['medias']) && is_array($data['medias'])) {
                foreach ($data['medias'] as $mediaData) {
                    $createData = [
                        'schedule_id' => $schedule->id,
                        'title' => $mediaData['title'] ?? null,
                        'media_type' => $mediaData['media_type'] ?? null,
                        'screen_id' => $mediaData['screen_id'] ?? null,
                        'duration_seconds' => $mediaData['duration_seconds'] ?? null,
                        'play_forever' => $mediaData['play_forever'] ?? false,
                    ];

                    // Add file path if provided
                    if (isset($mediaData['media_file']) && !empty($mediaData['media_file'])) {
                        $createData['media_file'] = $mediaData['media_file'];
                    }

                    // Handle date times
                    if (isset($mediaData['start_date_time']) && !empty($mediaData['start_date_time'])) {
                        try {
                            $createData['schedule_start_date_time'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaData['start_date_time']);
                        } catch (\Exception $e) {
                            $createData['schedule_start_date_time'] = null;
                        }
                    }

                    if (isset($mediaData['end_date_time']) && !empty($mediaData['end_date_time'])) {
                        try {
                            $createData['schedule_end_date_time'] = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $mediaData['end_date_time']);
                        } catch (\Exception $e) {
                            $createData['schedule_end_date_time'] = null;
                        }
                    }

                    // Overlap validation: only when screen and both dates are present
                    if (!empty($createData['screen_id']) && !empty($createData['schedule_start_date_time']) && !empty($createData['schedule_end_date_time'])) {
                        if ($this->screenHasOverlap($createData['screen_id'], $createData['schedule_start_date_time'], $createData['schedule_end_date_time'], null, $schedule->id)) {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Validation failed',
                                'errors' => [
                                    'overlap' => ['Selected time range overlaps with existing media on this screen.']
                                ]
                            ], 422);
                        }
                    }

                    ScheduleMedia::create($createData);
                }
            }

            DB::commit();

            // Load created schedule with relationships
            $createdSchedule = Schedule::with(['device', 'layout', 'medias.screen'])->find($schedule->id);

            return response()->json([
                'success' => true,
                'message' => 'Schedule created successfully',
                'schedule' => $createdSchedule
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Schedule creation with chunked upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->json()->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if a given time range overlaps existing media for the same screen.
     */
    private function screenHasOverlap($screenId, $startAt, $endAt, $excludeMediaId = null, $excludeScheduleId = null): bool
    {
        if (empty($screenId) || empty($startAt) || empty($endAt)) {
            return false;
        }

        $query = ScheduleMedia::where('screen_id', $screenId);

        if (!empty($excludeMediaId)) {
            $query->where('id', '!=', $excludeMediaId);
        }

        if (!empty($excludeScheduleId)) {
            $query->where('schedule_id', '!=', $excludeScheduleId);
        }

        $query->where(function ($q) use ($startAt, $endAt) {
            // Existing bounded range overlaps new range
            $q->where(function ($q2) use ($startAt, $endAt) {
                $q2->whereNotNull('schedule_start_date_time')
                    ->whereNotNull('schedule_end_date_time')
                    ->where('schedule_start_date_time', '<', $endAt)
                    ->where('schedule_end_date_time', '>', $startAt);
            })
                // Existing unbounded on both sides => overlaps everything
                ->orWhere(function ($q2) {
                    $q2->whereNull('schedule_start_date_time')
                        ->whereNull('schedule_end_date_time');
                })
                // Existing has only end -> starts from -inf to end
                ->orWhere(function ($q2) use ($startAt) {
                    $q2->whereNull('schedule_start_date_time')
                        ->whereNotNull('schedule_end_date_time')
                        ->where('schedule_end_date_time', '>', $startAt);
                })
                // Existing has only start -> from start to +inf
                ->orWhere(function ($q2) use ($endAt) {
                    $q2->whereNotNull('schedule_start_date_time')
                        ->whereNull('schedule_end_date_time')
                        ->where('schedule_start_date_time', '<', $endAt);
                });
        });

        return $query->exists();
    }
}
