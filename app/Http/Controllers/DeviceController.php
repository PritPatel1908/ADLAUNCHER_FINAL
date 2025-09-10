<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Device;
use App\Models\Company;
use App\Models\Location;
use App\Models\DeviceLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('device.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unique_id' => 'required|string|max:255|unique:devices,unique_id',
            'ip' => 'nullable|ip',
            'status' => 'required|in:delete,active,deactivate,block',
            'company_id' => 'nullable|exists:companies,id',
            'location_id' => 'nullable|exists:locations,id',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        try {
            DB::beginTransaction();

            $device = Device::create([
                'name' => $request->name,
                'unique_id' => $request->unique_id,
                'ip' => $request->ip,
                'company_id' => $request->company_id,
                'location_id' => $request->location_id,
                'area_id' => $request->area_id,
                'status' => $request->status === 'delete' ? Device::STATUS_DELETE : ($request->status === 'active' ? Device::STATUS_ACTIVATE : ($request->status === 'deactivate' ? Device::STATUS_INACTIVE : Device::STATUS_BLOCK)),
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Device created successfully',
                'device' => $device->load(['company', 'location', 'area'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device)
    {
        $device->load(['company', 'location', 'area', 'deviceLayouts', 'deviceScreens']);

        // Check if request is AJAX
        if (request()->ajax()) {
            return response()->json([
                'device' => $device
            ]);
        }

        return view('device.show', compact('device'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Device $device)
    {
        $device->load(['company', 'location', 'area']);
        return response()->json([
            'success' => true,
            'device' => $device
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Device $device)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unique_id' => 'required|string|max:255|unique:devices,unique_id,' . $device->id,
            'ip' => 'nullable|ip',
            'status' => 'required|in:delete,active,deactivate,block',
            'company_id' => 'nullable|exists:companies,id',
            'location_id' => 'nullable|exists:locations,id',
            'area_id' => 'nullable|exists:areas,id',
        ]);

        try {
            DB::beginTransaction();

            $device->update([
                'name' => $request->name,
                'unique_id' => $request->unique_id,
                'ip' => $request->ip,
                'company_id' => $request->company_id,
                'location_id' => $request->location_id,
                'area_id' => $request->area_id,
                'status' => $request->status === 'delete' ? Device::STATUS_DELETE : ($request->status === 'active' ? Device::STATUS_ACTIVATE : ($request->status === 'deactivate' ? Device::STATUS_INACTIVE : Device::STATUS_BLOCK)),
                'updated_by' => auth()->user()->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Device updated successfully',
                'device' => $device->load(['company', 'location', 'area'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device)
    {
        try {
            $device->delete();
            return response()->json([
                'success' => true,
                'message' => 'Device deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get devices data for DataTables
     */
    public function getData(Request $request)
    {
        $query = Device::with(['company', 'location', 'area', 'deviceLayouts', 'deviceScreens']);

        // Filters
        if ($request->filled('name_filter')) {
            $query->where('name', 'like', '%' . $request->name_filter . '%');
        }
        if ($request->filled('unique_id_filter')) {
            $query->where('unique_id', 'like', '%' . $request->unique_id_filter . '%');
        }
        if ($request->filled('ip_filter')) {
            $query->where('ip', 'like', '%' . $request->ip_filter . '%');
        }
        if ($request->filled('status_filter') && is_array($request->status_filter)) {
            $map = ['0' => 0, '1' => 1, '2' => 2, '3' => 3, 'delete' => 0, 'active' => 1, 'deactivate' => 2, 'block' => 3];
            $statusValues = [];
            foreach ($request->status_filter as $s) {
                $statusValues[] = $map[$s] ?? null;
            }
            $statusValues = array_filter($statusValues, function ($v) {
                return $v !== null;
            });
            if (!empty($statusValues)) {
                $query->whereIn('status', $statusValues);
            }
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        }

        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);

        $recordsTotal = (clone $query)->count();

        $searchValue = $request->input('search.value');
        if ($searchValue) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%$searchValue%")
                    ->orWhere('unique_id', 'like', "%$searchValue%")
                    ->orWhere('ip', 'like', "%$searchValue%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        $devices = $query
            ->skip($start)
            ->take($length)
            ->get();

        $data = $devices->map(function ($device) {
            return [
                'id' => $device->id,
                'name' => $device->name,
                'unique_id' => $device->unique_id,
                'company' => $device->company ? $device->company->name : null,
                'location' => $device->location ? $device->location->name : null,
                'area' => $device->area ? $device->area->name : null,
                'ip' => $device->ip,
                'layouts' => $device->deviceLayouts->map(function ($layout) {
                    return [
                        'id' => $layout->id,
                        'layout_name' => $layout->layout_name,
                        'layout_type' => $layout->layout_type,
                        'status' => $layout->status,
                        'created_at' => $layout->created_at,
                    ];
                }),
                'layouts_count' => $device->layouts_count,
                'active_layouts_count' => $device->active_layouts_count,
                'screens_count' => $device->screens_count,
                'status' => $device->status,
                'created_at' => $device->created_at,
                'updated_at' => $device->updated_at,
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
}
