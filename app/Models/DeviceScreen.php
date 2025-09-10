<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeviceScreen extends Model
{
    use HasFactory;

    protected $fillable = [
        'screen_no',
        'screen_height',
        'screen_width',
        'device_id',
        'layout_id',
    ];

    protected $casts = [
        'screen_no' => 'integer',
        'screen_height' => 'integer',
        'screen_width' => 'integer',
        'device_id' => 'integer',
        'layout_id' => 'integer',
    ];

    /**
     * Parent device relation.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Layout relation.
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(DeviceLayout::class, 'layout_id');
    }

    /**
     * Check if screen dimensions conflict with existing screens in the same device
     */
    public function hasDimensionConflict(int $height, int $width, int $deviceId, ?int $excludeId = null): bool
    {
        $query = self::where('device_id', $deviceId)
            ->where('screen_height', $height)
            ->where('screen_width', $width);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get conflicting screens for given dimensions
     */
    public function getConflictingScreens(int $height, int $width, int $deviceId, ?int $excludeId = null)
    {
        $query = self::where('device_id', $deviceId)
            ->where('screen_height', $height)
            ->where('screen_width', $width);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get();
    }

    /**
     * Check if screen layout configuration is valid for the given layout type
     */
    public function validateLayoutConfiguration(int $layoutId, int $deviceId, ?int $excludeId = null): array
    {
        $layout = DeviceLayout::find($layoutId);
        if (!$layout) {
            return ['valid' => false, 'message' => 'Layout not found'];
        }

        $screens = self::where('device_id', $deviceId)
            ->where('layout_id', $layoutId);

        if ($excludeId) {
            $screens->where('id', '!=', $excludeId);
        }

        $screens = $screens->get();

        // Check if layout allows the number of screens
        if ($screens->count() >= $layout->max_screens) {
            return [
                'valid' => false,
                'message' => "Layout type '{$layout->layout_type_name}' allows maximum {$layout->max_screens} screen(s). Currently has {$screens->count()} screen(s)."
            ];
        }

        // Validate screen configuration based on layout type
        return $this->validateScreenPositions($screens, $layout->layout_type);
    }

    /**
     * Validate screen positions based on layout type to prevent conflicts
     */
    private function validateScreenPositions($screens, int $layoutType): array
    {
        switch ($layoutType) {
            case DeviceLayout::LAYOUT_TYPE_FULL_SCREEN:
                return $this->validateFullScreenLayout($screens);

            case DeviceLayout::LAYOUT_TYPE_SPLIT_SCREEN:
                return $this->validateSplitScreenLayout($screens);

            case DeviceLayout::LAYOUT_TYPE_THREE_GRID_SCREEN:
                return $this->validateThreeGridLayout($screens);

            case DeviceLayout::LAYOUT_TYPE_FOUR_GRID_SCREEN:
                return $this->validateFourGridLayout($screens);

            default:
                return ['valid' => false, 'message' => 'Unknown layout type'];
        }
    }

    /**
     * Validate full screen layout (1 screen)
     */
    private function validateFullScreenLayout($screens): array
    {
        if ($screens->count() > 1) {
            return ['valid' => false, 'message' => 'Full screen layout can only have 1 screen'];
        }

        if ($screens->count() === 1) {
            $screen = $screens->first();
            // For full screen, dimensions should be reasonable (not too small)
            if ($screen->screen_width < 100 || $screen->screen_height < 50) {
                return ['valid' => false, 'message' => 'Full screen dimensions are too small. Minimum width: 100, height: 50'];
            }
        }

        return ['valid' => true, 'message' => 'Full screen layout is valid'];
    }

    /**
     * Validate split screen layout (2 screens)
     */
    private function validateSplitScreenLayout($screens): array
    {
        if ($screens->count() > 2) {
            return ['valid' => false, 'message' => 'Split screen layout can only have 2 screens'];
        }

        if ($screens->count() === 2) {
            $screen1 = $screens->first();
            $screen2 = $screens->last();

            // Check for conflicts in split screen layout
            $conflict = $this->checkSplitScreenConflict($screen1, $screen2);
            if (!$conflict['valid']) {
                return $conflict;
            }
        }

        return ['valid' => true, 'message' => 'Split screen layout is valid'];
    }

    /**
     * Validate three grid layout (3 screens)
     */
    private function validateThreeGridLayout($screens): array
    {
        if ($screens->count() > 3) {
            return ['valid' => false, 'message' => 'Three grid layout can only have 3 screens'];
        }

        if ($screens->count() === 3) {
            $screenList = $screens->sortBy('screen_no')->values();

            // Check for conflicts in three grid layout
            $conflict = $this->checkThreeGridConflict($screenList);
            if (!$conflict['valid']) {
                return $conflict;
            }
        }

        return ['valid' => true, 'message' => 'Three grid layout is valid'];
    }

    /**
     * Validate four grid layout (4 screens)
     */
    private function validateFourGridLayout($screens): array
    {
        if ($screens->count() > 4) {
            return ['valid' => false, 'message' => 'Four grid layout can only have 4 screens'];
        }

        if ($screens->count() === 4) {
            $screenList = $screens->sortBy('screen_no')->values();

            // Check for conflicts in four grid layout
            $conflict = $this->checkFourGridConflict($screenList);
            if (!$conflict['valid']) {
                return $conflict;
            }
        }

        return ['valid' => true, 'message' => 'Four grid layout is valid'];
    }

    /**
     * Check for conflicts in split screen layout
     */
    private function checkSplitScreenConflict($screen1, $screen2): array
    {
        // No validation - allow any dimensions
        return ['valid' => true, 'message' => 'Split screen layout configuration is valid'];
    }

    /**
     * Check for conflicts in three grid layout
     */
    private function checkThreeGridConflict($screens): array
    {
        // No validation - allow any dimensions
        return ['valid' => true, 'message' => 'Three grid layout configuration is valid'];
    }

    /**
     * Check for conflicts in four grid layout
     */
    private function checkFourGridConflict($screens): array
    {
        // No validation - allow any dimensions
        return ['valid' => true, 'message' => 'Four grid layout configuration is valid'];
    }

    /**
     * Check if adding a new screen would cause conflicts
     */
    public function checkScreenAdditionConflict(int $height, int $width, int $deviceId, int $layoutId, ?int $excludeId = null): array
    {
        // No validation - allow any dimensions
        return ['valid' => true, 'message' => 'Screen addition is valid'];
    }
}
