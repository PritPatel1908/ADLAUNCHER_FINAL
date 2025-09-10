<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\User;
use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $areas = Area::latest()->get();
        return view('area.index', compact('areas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('area.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'code' => 'nullable|string|max:100',
            'status' => 'nullable|integer|in:0,1,2,3',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,id',
        ]);

        // Set the created_by field to the current authenticated user's ID
        $validated['created_by'] = Auth::user()->id;
        $validated['status'] = $validated['status'] ?? 1; // Default to Activate (1)

        // Create the area
        $area = Area::create($validated);

        // Handle locations relationship
        if (isset($validated['location_ids'])) {
            $area->locations()->attach($validated['location_ids']);
        }

        // Handle companies relationship
        if (isset($validated['company_ids'])) {
            $area->companies()->attach($validated['company_ids']);
        }

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Area created successfully',
                'area' => $area->load(['locations', 'companies'])
            ]);
        }

        return redirect()->route('area.index')->with('success', 'Area created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $area = Area::with(['locations', 'companies'])->findOrFail($id);

        // Check if request is AJAX
        if (request()->ajax()) {
            return response()->json([
                'area' => $area
            ]);
        }

        return view('area.show', compact('area'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $area = Area::with(['locations', 'companies'])->findOrFail($id);

            // Check if request is AJAX
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'area' => $area
                ]);
            }

            return redirect()->route('area.index');
        } catch (\Exception $e) {
            Log::error('Error in AreaController::edit: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load area data. Please try again.'
                ], 500);
            }

            return redirect()->route('area.index')->with('error', 'Failed to load area data.');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $area = Area::findOrFail($id);

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'code' => 'nullable|string|max:100',
            'status' => 'nullable|integer|in:0,1,2,3',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,id',
        ]);

        // Set the updated_by field to the current authenticated user's ID
        $validated['updated_by'] = Auth::user()->id;
        $validated['status'] = $validated['status'] ?? 1; // Default to Activate (1)

        // Update the area
        $area->update($validated);

        // Handle locations relationship
        if (isset($validated['location_ids'])) {
            $area->locations()->sync($validated['location_ids']);
        }

        // Handle companies relationship
        if (isset($validated['company_ids'])) {
            $area->companies()->sync($validated['company_ids']);
        }

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Area updated successfully',
                'area' => $area->load(['locations', 'companies', 'createdByUser', 'updatedByUser'])
            ]);
        }

        // Check if request came from show page
        if ($request->has('from_show') || strpos($request->header('referer'), '/area/') !== false) {
            return redirect()->route('area.show', $area->id)->with('success', 'Area updated successfully');
        }

        return redirect()->route('area.index')->with('success', 'Area updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $area = Area::findOrFail($id);
        $area->delete();

        // Check if request is AJAX
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Area deleted successfully'
            ]);
        }

        return redirect()->route('area.index')->with('success', 'Area deleted successfully');
    }

    /**
     * Get data for DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        try {
            $query = Area::with(['locations', 'companies', 'createdByUser', 'updatedByUser']);

            // Apply search filter
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('description', 'like', "%{$searchValue}%")
                        ->orWhere('code', 'like', "%{$searchValue}%");
                });
            }

            // Apply column-specific filtering
            if ($request->has('name_filter') && !empty($request->name_filter)) {
                $query->where('name', 'like', "%{$request->name_filter}%");
            }

            if ($request->has('description_filter') && !empty($request->description_filter)) {
                $query->where('description', 'like', "%{$request->description_filter}%");
            }

            if ($request->has('code_filter') && !empty($request->code_filter)) {
                $query->where('code', 'like', "%{$request->code_filter}%");
            }

            // Apply date range filter
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                $endDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            // Apply status filter
            if ($request->has('status_filter') && !empty($request->status_filter)) {
                $statusFilter = $request->status_filter;
                if (!is_array($statusFilter)) {
                    $statusFilter = [$statusFilter];
                }

                $query->where(function ($q) use ($statusFilter) {
                    foreach ($statusFilter as $status) {
                        if (is_numeric($status)) {
                            $q->orWhere('status', (int) $status);
                        } else {
                            // Handle text-based status filters
                            switch (strtolower($status)) {
                                case 'activate':
                                case 'active':
                                    $q->orWhere('status', 1);
                                    break;
                                case 'inactive':
                                    $q->orWhere('status', 2);
                                    break;
                                case 'block':
                                case 'blocked':
                                    $q->orWhere('status', 3);
                                    break;
                                case 'delete':
                                case 'deleted':
                                    $q->orWhere('status', 0);
                                    break;
                            }
                        }
                    }
                });
            }

            // Count filtered records
            $recordsFiltered = $query->count();

            // Apply ordering
            if ($request->has('order') && !empty($request->order)) {
                foreach ($request->order as $order) {
                    $columnName = $this->getColumnName($request->columns[$order['column']]['data']);
                    if ($columnName) {
                        $query->orderBy($columnName, $order['dir']);
                    }
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Apply pagination
            $query->skip($request->start)->take($request->length);

            // Get results
            $areas = $query->get();

            // Format data for DataTables
            $data = [];
            // $canViewAuditFields = \App\Helpers\PermissionHelper::canViewAuditFields();

            foreach ($areas as $area) {
                $areaData = [
                    'id' => $area->id,
                    'name' => $area->name,
                    'description' => $area->description,
                    'code' => $area->code,
                    'created_at' => $area->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $area->updated_at->format('Y-m-d H:i:s'),
                    'status' => $area->status_text,
                    'status_value' => $area->status,
                    'locations_count' => $area->locations->count(),
                    'companies_count' => $area->companies->count(),
                ];

                // Only include audit fields if user has permission
                // if ($canViewAuditFields) {
                $areaData['created_by'] = $area->createdByUser ? $area->createdByUser->name : 'N/A';
                $areaData['updated_by'] = $area->updatedByUser ? $area->updatedByUser->name : 'N/A';
                // }

                $data[] = $areaData;
            }

            return response()->json([
                'draw' => $request->draw,
                'recordsTotal' => Area::count(),
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in AreaController::getData: ' . $e->getMessage());
            return response()->json([
                'draw' => $request->draw ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load area data. Please try again.'
            ], 500);
        }
    }

    /**
     * Get the actual column name from DataTables column data.
     *
     * @param  string  $columnData
     * @return string|null
     */
    private function getColumnName($columnData)
    {
        $columnMap = [
            'name' => 'name',
            'description' => 'description',
            'code' => 'code',
            'status' => 'status',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];

        // Only include audit columns if user has permission
        // if (\App\Helpers\PermissionHelper::canViewAuditFields()) {
        $columnMap['created_by'] = 'created_by';
        $columnMap['updated_by'] = 'updated_by';
        // }

        return $columnMap[$columnData] ?? null;
    }
}
