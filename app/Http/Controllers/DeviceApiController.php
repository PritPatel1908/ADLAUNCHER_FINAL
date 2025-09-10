<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Device;
use App\Models\Schedule;
use App\Models\DeviceLayout;
use App\Models\DeviceScreen;
use Illuminate\Http\Request;
use App\Models\ScheduleMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DeviceApiController extends Controller
{
    /**
     * Get device authentication status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAuth(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->input('device_id');

            if (!$deviceId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'device_id parameter is required'
                ], 400);
            }

            // Use cache for better performance with multiple concurrent requests
            $cacheKey = "device_auth_{$deviceId}";
            $device = Cache::remember($cacheKey, 300, function () use ($deviceId) { // 5 minutes cache
                return Device::select('id', 'name', 'unique_id', 'status')
                    ->where('unique_id', $deviceId)
                    ->first();
            });

            if (!$device) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Device authenticated successfully',
                'device_id' => $device->id,
                'device_name' => $device->name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get new data for device
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getNewData(Request $request): JsonResponse
    {
        try {
            $deviceId = $request->input('device_id');

            if (!$deviceId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'device_id parameter is required'
                ], 400);
            }

            // Use cache for better performance with multiple concurrent requests
            $cacheKey = "device_data_{$deviceId}";
            $responseData = Cache::remember($cacheKey, 60, function () use ($deviceId) { // 1 minute cache
                return $this->getDeviceData($deviceId);
            });

            if ($responseData === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Device not found'
                ], 404);
            }

            if (isset($responseData['error'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => $responseData['error']
                ], $responseData['status_code']);
            }

            // Reformat response to required schema
            $deviceUniqueId = $responseData[0]['device_unique_id'] ?? $deviceId;
            $layoutType = $responseData[0]['layout_type'] ?? 'unknown';

            // Map to desired schema and drop entries with no medias
            $screens = array_values(array_filter(array_map(function ($item) {
                $mapped = [
                    'screen_no' => isset($item['screen_no']) ? (string) $item['screen_no'] : null,
                    'screen_height' => isset($item['screen_height']) ? (string) $item['screen_height'] : null,
                    'screen_width' => isset($item['screen_width']) ? (string) $item['screen_width'] : null,
                    'medias' => $item['medias'] ?? []
                ];
                return !empty($mapped['medias']) ? $mapped : null;
            }, $responseData)));

            return response()->json([
                'status' => 'success',
                'message' => 'Data retrieved successfully',
                'device_unique_id' => $deviceUniqueId,
                'layout_type' => $layoutType,
                'data' => $screens
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get device data with optimized queries
     *
     * @param string $deviceId
     * @return array|false
     */
    private function getDeviceData(string $deviceId)
    {
        // Single optimized query to get all required data
        $deviceData = DB::table('devices as d')
            ->leftJoin('device_layouts as dl', function ($join) {
                $join->on('d.id', '=', 'dl.device_id')
                    ->where('dl.status', '=', DeviceLayout::STATUS_ACTIVE);
            })
            ->leftJoin('device_screens as ds', 'dl.id', '=', 'ds.layout_id')
            ->leftJoin('schedules as s', function ($join) {
                $join->on('d.id', '=', 's.device_id')
                    ->on('dl.id', '=', 's.layout_id');
            })
            ->leftJoin('schedule_medias as sm', function ($join) {
                // Join only by schedule; handle screen matching (or null) in PHP to avoid dropping rows
                $join->on('s.id', '=', 'sm.schedule_id');
            })
            ->select(
                'd.id as device_id',
                'd.unique_id as device_unique_id',
                'd.status as device_status',
                'dl.layout_type',
                'ds.id as ds_id',
                'ds.screen_no',
                'ds.screen_height',
                'ds.screen_width',
                'sm.screen_id as media_screen_id',
                'sm.schedule_start_date_time',
                'sm.schedule_end_date_time',
                'sm.play_forever',
                'sm.duration_seconds',
                'sm.media_type',
                'sm.title',
                'sm.media_file'
            )
            ->where('d.unique_id', $deviceId)
            ->get();

        if ($deviceData->isEmpty()) {
            return false;
        }

        $firstRow = $deviceData->first();

        // Check if device is active
        if ($firstRow->device_status != Device::STATUS_ACTIVATE) {
            return ['error' => 'Device is not active', 'status_code' => 403];
        }

        // Check if device has active layout (layout_type can be 0 for full screen, so check null)
        if (is_null($firstRow->layout_type)) {
            return ['error' => 'No active layout found for device', 'status_code' => 404];
        }

        // Group data by screen first, then organize media by schedule within each screen
        $screens = [];

        foreach ($deviceData as $row) {
            // Include entries that either have a start time, are marked to play forever, or at least have media
            if ($row->schedule_start_date_time || $row->play_forever || $row->media_file) {
                $screenKey = $row->screen_no ?? 'noscreen';

                if (!isset($screens[$screenKey])) {
                    $screens[$screenKey] = [
                        'device_unique_id' => $row->device_unique_id,
                        'layout_type' => $this->getLayoutTypeName((int) $row->layout_type),
                        'screen_no' => $row->screen_no,
                        'screen_height' => $row->screen_height,
                        'screen_width' => $row->screen_width,
                        'medias' => []
                    ];
                }

                // Add media if exists and screen matches (or media is for all screens)
                $mediaAppliesToThisScreen = is_null($row->media_screen_id) || ($row->media_screen_id == $row->ds_id);
                if ($row->media_file && $mediaAppliesToThisScreen) {
                    $screens[$screenKey]['medias'][] = [
                        'media_type' => $row->media_type,
                        'title' => $row->title,
                        'duration_seconds' => isset($row->duration_seconds) ? (int) $row->duration_seconds : null,
                        'schedule_start_date_time' => $row->schedule_start_date_time,
                        'schedule_end_date_time' => $row->schedule_end_date_time,
                        'play_forever' => (bool) $row->play_forever,
                        'media_file' => $row->media_file,
                        'media_url' => $this->getMediaUrl($row->media_file)
                    ];
                }
            }
        }

        if (empty($screens)) {
            return ['error' => 'No schedules found for device', 'status_code' => 404];
        }

        return array_values($screens);
    }

    /**
     * Get layout type name from constant
     *
     * @param int $layoutType
     * @return string
     */
    private function getLayoutTypeName(int $layoutType): string
    {
        return match ($layoutType) {
            DeviceLayout::LAYOUT_TYPE_FULL_SCREEN => 'full_screen',
            DeviceLayout::LAYOUT_TYPE_SPLIT_SCREEN => 'split_screen',
            DeviceLayout::LAYOUT_TYPE_THREE_GRID_SCREEN => 'three_grid_screen',
            DeviceLayout::LAYOUT_TYPE_FOUR_GRID_SCREEN => 'four_grid_screen',
            default => 'unknown'
        };
    }

    /**
     * Generate media URL for file access
     *
     * @param string $mediaFile
     * @return string
     */
    private function getMediaUrl(string $mediaFile): string
    {
        // Build a temporary signed download URL that points to the download endpoint
        // mediaFile here is typically like: schedule_media/filename.mp4
        $expiresAt = now()->addMinutes(30);
        return URL::temporarySignedRoute('api.device.download', $expiresAt, [
            'path' => $mediaFile,
        ]);
    }

    /**
     * Signed download handler for device media
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function downloadMedia(Request $request)
    {
        // Validate signature
        if (!$request->hasValidSignature()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired download link'
            ], 401);
        }

        $relativePath = $request->query('path');
        if (!$relativePath) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing media path'
            ], 400);
        }

        // Files are stored on the public disk; use the provided relative path as-is
        $disk = Storage::disk('public');
        $fullPath = ltrim($relativePath, '/');

        if (!$disk->exists($fullPath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found'
            ], 404);
        }

        $filename = basename($fullPath);
        $absolutePath = method_exists($disk, 'path') ? $disk->path($fullPath) : storage_path('app/public/' . $fullPath);
        $mimeType = File::exists($absolutePath) ? (File::mimeType($absolutePath) ?: 'application/octet-stream') : 'application/octet-stream';

        return response()->stream(function () use ($disk, $fullPath) {
            $stream = $disk->readStream($fullPath);
            if ($stream !== false) {
                fpassthru($stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=0, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Clear device cache (useful when device data is updated)
     *
     * @param string $deviceId
     * @return void
     */
    public function clearDeviceCache(string $deviceId): void
    {
        Cache::forget("device_auth_{$deviceId}");
        Cache::forget("device_data_{$deviceId}");
    }

    /**
     * Get API status and performance metrics
     *
     * @return JsonResponse
     */
    public function getApiStatus(): JsonResponse
    {
        try {
            $cacheStats = [
                'cache_driver' => config('cache.default'),
                'cache_prefix' => config('cache.prefix'),
            ];

            // Get some basic stats
            $deviceCount = Device::count();
            $activeDeviceCount = Device::where('status', Device::STATUS_ACTIVATE)->count();
            $scheduleCount = Schedule::count();

            return response()->json([
                'status' => 'success',
                'api_version' => '1.0',
                'timestamp' => now()->toISOString(),
                'cache_stats' => $cacheStats,
                'database_stats' => [
                    'total_devices' => $deviceCount,
                    'active_devices' => $activeDeviceCount,
                    'total_schedules' => $scheduleCount,
                ],
                'endpoints' => [
                    'get_auth' => '/api/device/get_auth',
                    'get_new_data' => '/api/device/get_new_data',
                    'api_status' => '/api/device/status'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
