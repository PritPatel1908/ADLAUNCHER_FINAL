<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceScreen;
use App\Models\DeviceLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceScreenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $screens = DeviceScreen::with(['device', 'layout'])->get();
        return response()->json([
            'success' => true,
            'screens' => $screens,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'screen_no' => 'required|integer|min:1|max:255',
            'screen_height' => 'required|integer|min:1',
            'screen_width' => 'required|integer|min:1',
            'device_id' => 'required|exists:devices,id',
            'layout_id' => 'required|exists:device_layouts,id',
        ]);

        try {
            DB::beginTransaction();

            // Get the layout to check its type and screen limits
            $layout = DeviceLayout::findOrFail($request->layout_id);

            // Check if layout allows adding more screens
            if (!$layout->canAddMoreScreens()) {
                return response()->json([
                    'success' => false,
                    'message' => "This layout type ({$layout->layout_type_name}) allows maximum {$layout->max_screens} screen(s). You have already reached the limit."
                ], 422);
            }

            // Ensure screen_no is unique per device
            $exists = DeviceScreen::where('device_id', $request->device_id)
                ->where('screen_no', $request->screen_no)
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Screen number already exists for this device.'
                ], 422);
            }

            // Check for screen conflicts using enhanced validation
            $deviceScreen = new DeviceScreen();
            $conflictCheck = $deviceScreen->checkScreenAdditionConflict(
                $request->screen_height,
                $request->screen_width,
                $request->device_id,
                $request->layout_id
            );

            if (!$conflictCheck['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $conflictCheck['message']
                ], 422);
            }

            $screen = DeviceScreen::create([
                'screen_no' => $request->screen_no,
                'screen_height' => $request->screen_height,
                'screen_width' => $request->screen_width,
                'device_id' => $request->device_id,
                'layout_id' => $request->layout_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Device screen created successfully',
                'screen' => $screen->load(['device', 'layout']),
                'layout_info' => [
                    'max_screens' => $layout->max_screens,
                    'remaining_slots' => $layout->remaining_screen_slots,
                    'layout_type' => $layout->layout_type_name
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create device screen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DeviceScreen $deviceScreen)
    {
        $deviceScreen->load(['device', 'layout']);
        return response()->json([
            'success' => true,
            'screen' => $deviceScreen,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeviceScreen $deviceScreen)
    {
        $request->validate([
            'screen_no' => 'required|integer|min:1|max:255',
            'screen_height' => 'required|integer|min:1',
            'screen_width' => 'required|integer|min:1',
            'layout_id' => 'required|exists:device_layouts,id',
        ]);

        try {
            DB::beginTransaction();

            // Get the layout to check its type and screen limits
            $layout = DeviceLayout::findOrFail($request->layout_id);

            // If changing layout, check if new layout allows this screen
            if ($deviceScreen->layout_id != $request->layout_id) {
                // Count screens in the new layout (excluding current screen)
                $screensInNewLayout = DeviceScreen::where('layout_id', $request->layout_id)
                    ->where('id', '!=', $deviceScreen->id)
                    ->count();

                if ($screensInNewLayout >= $layout->max_screens) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot move screen to this layout. Layout type ({$layout->layout_type_name}) allows maximum {$layout->max_screens} screen(s) and already has {$screensInNewLayout} screen(s)."
                    ], 422);
                }
            }

            // Ensure screen_no uniqueness per device when updating
            $exists = DeviceScreen::where('device_id', $deviceScreen->device_id)
                ->where('screen_no', $request->screen_no)
                ->where('id', '!=', $deviceScreen->id)
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Screen number already exists for this device.'
                ], 422);
            }

            // Check for screen conflicts using enhanced validation (excluding current screen)
            $conflictCheck = $deviceScreen->checkScreenAdditionConflict(
                $request->screen_height,
                $request->screen_width,
                $deviceScreen->device_id,
                $request->layout_id,
                $deviceScreen->id
            );

            if (!$conflictCheck['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $conflictCheck['message']
                ], 422);
            }

            $deviceScreen->update([
                'screen_no' => $request->screen_no,
                'screen_height' => $request->screen_height,
                'screen_width' => $request->screen_width,
                'layout_id' => $request->layout_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Device screen updated successfully',
                'screen' => $deviceScreen->load(['device', 'layout']),
                'layout_info' => [
                    'max_screens' => $layout->max_screens,
                    'remaining_slots' => $layout->remaining_screen_slots,
                    'layout_type' => $layout->layout_type_name
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update device screen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeviceScreen $deviceScreen)
    {
        try {
            $deviceScreen->delete();
            return response()->json([
                'success' => true,
                'message' => 'Device screen deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete device screen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get screens for a specific device
     */
    public function getDeviceScreens(Device $device)
    {
        $query = $device->deviceScreens()->with('layout')->orderBy('screen_no');
        // Optional filter by layout_id when provided
        $layoutId = request('layout_id');
        if ($layoutId !== null && $layoutId !== '') {
            $query->where('layout_id', (int) $layoutId);
        }
        $screens = $query->get();

        // Get layout information if layout_id is provided
        $layoutInfo = null;
        if ($layoutId !== null && $layoutId !== '') {
            $layout = DeviceLayout::find($layoutId);
            if ($layout) {
                $layoutInfo = [
                    'id' => $layout->id,
                    'name' => $layout->layout_name,
                    'type' => $layout->layout_type,
                    'type_name' => $layout->layout_type_name,
                    'max_screens' => $layout->max_screens,
                    'current_screens' => $screens->count(),
                    'remaining_slots' => $layout->remaining_screen_slots,
                    'can_add_more' => $layout->canAddMoreScreens()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'screens' => $screens,
            'layout_info' => $layoutInfo,
            'counts' => [
                'total' => $device->screens_count,
            ]
        ]);
    }

    /**
     * Get screens for a specific layout
     */
    public function getLayoutScreens(DeviceLayout $layout)
    {
        $screens = $layout->screens()->with('device')->orderBy('screen_no')->get();

        $layoutInfo = [
            'id' => $layout->id,
            'name' => $layout->layout_name,
            'type' => $layout->layout_type,
            'type_name' => $layout->layout_type_name,
            'max_screens' => $layout->max_screens,
            'current_screens' => $screens->count(),
            'remaining_slots' => $layout->remaining_screen_slots,
            'can_add_more' => $layout->canAddMoreScreens()
        ];

        return response()->json([
            'success' => true,
            'screens' => $screens,
            'layout_info' => $layoutInfo,
            'counts' => [
                'total' => $screens->count(),
            ]
        ]);
    }

    /**
     * Validate screen configuration before adding
     */
    public function validateScreenConfiguration(Request $request)
    {
        $request->validate([
            'screen_height' => 'required|integer|min:1',
            'screen_width' => 'required|integer|min:1',
            'device_id' => 'required|exists:devices,id',
            'layout_id' => 'required|exists:device_layouts,id',
            'screen_no' => 'required|integer|min:1|max:255',
        ]);

        $deviceScreen = new DeviceScreen();

        // Check screen number uniqueness
        $screenNoExists = DeviceScreen::where('device_id', $request->device_id)
            ->where('screen_no', $request->screen_no)
            ->exists();

        if ($screenNoExists) {
            return response()->json([
                'success' => false,
                'message' => 'Screen number already exists for this device.'
            ], 422);
        }

        // Check for conflicts
        $conflictCheck = $deviceScreen->checkScreenAdditionConflict(
            $request->screen_height,
            $request->screen_width,
            $request->device_id,
            $request->layout_id
        );

        return response()->json([
            'success' => $conflictCheck['valid'],
            'message' => $conflictCheck['message'],
            'validation_details' => [
                'screen_height' => $request->screen_height,
                'screen_width' => $request->screen_width,
                'device_id' => $request->device_id,
                'layout_id' => $request->layout_id,
                'screen_no' => $request->screen_no,
            ]
        ]);
    }

    /**
     * Get layout validation rules and constraints
     */
    public function getLayoutValidationRules(DeviceLayout $layout)
    {
        $rules = [
            'layout_type' => $layout->layout_type,
            'layout_type_name' => $layout->layout_type_name,
            'max_screens' => $layout->max_screens,
            'current_screens' => $layout->screens()->count(),
            'remaining_slots' => $layout->remaining_screen_slots,
        ];

        // Add specific rules based on layout type
        switch ($layout->layout_type) {
            case DeviceLayout::LAYOUT_TYPE_FULL_SCREEN:
                $rules['constraints'] = [
                    'max_screens' => 1,
                    'min_width' => 100,
                    'min_height' => 50,
                    'description' => 'Single full screen layout'
                ];
                break;

            case DeviceLayout::LAYOUT_TYPE_SPLIT_SCREEN:
                $rules['constraints'] = [
                    'max_screens' => 2,
                    'arrangement' => 'Any arrangement allowed',
                    'requirements' => 'No dimension restrictions',
                    'description' => 'Two screens with complete freedom'
                ];
                break;

            case DeviceLayout::LAYOUT_TYPE_THREE_GRID_SCREEN:
                $rules['constraints'] = [
                    'max_screens' => 3,
                    'arrangement' => 'Any arrangement allowed',
                    'requirements' => 'No dimension restrictions',
                    'description' => 'Three screens with complete freedom (e.g., 50x50, 50x50, 50x100)'
                ];
                break;

            case DeviceLayout::LAYOUT_TYPE_FOUR_GRID_SCREEN:
                $rules['constraints'] = [
                    'max_screens' => 4,
                    'arrangement' => 'Any arrangement allowed',
                    'requirements' => 'No dimension restrictions',
                    'description' => 'Four screens with complete freedom'
                ];
                break;

            default:
                $rules['constraints'] = [
                    'description' => 'Unknown layout type'
                ];
                break;
        }

        return response()->json([
            'success' => true,
            'layout_rules' => $rules
        ]);
    }
}
