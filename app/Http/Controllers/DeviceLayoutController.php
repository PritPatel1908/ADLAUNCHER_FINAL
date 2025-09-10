<?php

namespace App\Http\Controllers;

use App\Models\DeviceLayout;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceLayoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $layouts = DeviceLayout::with('device')->get();
        return response()->json([
            'success' => true,
            'layouts' => $layouts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'layout_name' => 'required|string|max:255',
            'layout_type' => 'required|integer|in:0,1,2,3',
            'device_id' => 'required|exists:devices,id',
            'status' => 'required|integer|in:0,1,2,3',
        ]);

        try {
            DB::beginTransaction();

            // If setting this layout to Active, inactivate other active layouts of the same device first
            if ((int) $request->status === DeviceLayout::STATUS_ACTIVE) {
                DeviceLayout::where('device_id', $request->device_id)
                    ->where('status', DeviceLayout::STATUS_ACTIVE)
                    ->update(['status' => DeviceLayout::STATUS_INACTIVE]);
            }

            $deviceLayout = DeviceLayout::create([
                'layout_name' => $request->layout_name,
                'layout_type' => $request->layout_type,
                'device_id' => $request->device_id,
                'status' => $request->status,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Device layout created successfully',
                'layout' => $deviceLayout->load('device')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create device layout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DeviceLayout $deviceLayout)
    {
        $deviceLayout->load('device');
        return response()->json([
            'success' => true,
            'layout' => $deviceLayout
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeviceLayout $deviceLayout)
    {
        $request->validate([
            'layout_name' => 'required|string|max:255',
            'layout_type' => 'required|integer|in:0,1,2,3',
            'status' => 'required|integer|in:0,1,2,3',
        ]);

        try {
            DB::beginTransaction();

            // If updating status to Active, inactivate other active layouts on the same device
            if ((int) $request->status === DeviceLayout::STATUS_ACTIVE) {
                DeviceLayout::where('device_id', $deviceLayout->device_id)
                    ->where('id', '!=', $deviceLayout->id)
                    ->where('status', DeviceLayout::STATUS_ACTIVE)
                    ->update(['status' => DeviceLayout::STATUS_INACTIVE]);
            }

            $originalLayoutType = (int) $deviceLayout->layout_type;
            $newLayoutType = (int) $request->layout_type;

            $deviceLayout->update([
                'layout_name' => $request->layout_name,
                'layout_type' => $newLayoutType,
                'status' => $request->status,
            ]);

            // If layout type reduced, prune extra screens keeping the first N by screen_no
            $oldMax = match ($originalLayoutType) {
                DeviceLayout::LAYOUT_TYPE_FULL_SCREEN => 1,
                DeviceLayout::LAYOUT_TYPE_SPLIT_SCREEN => 2,
                DeviceLayout::LAYOUT_TYPE_THREE_GRID_SCREEN => 3,
                DeviceLayout::LAYOUT_TYPE_FOUR_GRID_SCREEN => 4,
                default => 1,
            };
            $newMax = match ($newLayoutType) {
                DeviceLayout::LAYOUT_TYPE_FULL_SCREEN => 1,
                DeviceLayout::LAYOUT_TYPE_SPLIT_SCREEN => 2,
                DeviceLayout::LAYOUT_TYPE_THREE_GRID_SCREEN => 3,
                DeviceLayout::LAYOUT_TYPE_FOUR_GRID_SCREEN => 4,
                default => 1,
            };

            if ($newMax < $oldMax) {
                // Get screens ordered by screen_no; keep first $newMax, delete rest (and their media)
                $screens = $deviceLayout->screens()->orderBy('screen_no')->get();
                $idsToKeep = $screens->take($newMax)->pluck('id')->all();
                $idsToDelete = $screens->skip($newMax)->pluck('id')->all();

                if (!empty($idsToDelete)) {
                    // Delete related schedule media first
                    \App\Models\ScheduleMedia::whereIn('screen_id', $idsToDelete)->delete();
                    // Delete the screens
                    \App\Models\DeviceScreen::whereIn('id', $idsToDelete)->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Device layout updated successfully',
                'layout' => $deviceLayout->load('device')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update device layout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeviceLayout $deviceLayout)
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
     * Get layouts for a specific device
     */
    public function getDeviceLayouts(Device $device)
    {
        // Optionally filter by status if provided (e.g., status=1 for Active)
        $query = $device->deviceLayouts();
        $status = request('status');
        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }
        $layouts = $query->get();
        return response()->json([
            'success' => true,
            'layouts' => $layouts,
            'counts' => [
                'total' => $device->layouts_count,
                'active' => $device->active_layouts_count,
                'inactive' => $device->getLayoutsByStatus(2),
                'blocked' => $device->getLayoutsByStatus(3)
            ]
        ]);
    }

    /**
     * Get layout statistics
     */
    public function getLayoutStats()
    {
        $stats = [
            'total_layouts' => DeviceLayout::count(),
            'active_layouts' => DeviceLayout::where('status', DeviceLayout::STATUS_ACTIVE)->count(),
            'inactive_layouts' => DeviceLayout::where('status', DeviceLayout::STATUS_INACTIVE)->count(),
            'blocked_layouts' => DeviceLayout::where('status', DeviceLayout::STATUS_BLOCK)->count(),
            'layouts_by_type' => [
                'full_screen' => DeviceLayout::where('layout_type', DeviceLayout::LAYOUT_TYPE_FULL_SCREEN)->count(),
                'split_screen' => DeviceLayout::where('layout_type', DeviceLayout::LAYOUT_TYPE_SPLIT_SCREEN)->count(),
                'three_grid' => DeviceLayout::where('layout_type', DeviceLayout::LAYOUT_TYPE_THREE_GRID_SCREEN)->count(),
                'four_grid' => DeviceLayout::where('layout_type', DeviceLayout::LAYOUT_TYPE_FOUR_GRID_SCREEN)->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
