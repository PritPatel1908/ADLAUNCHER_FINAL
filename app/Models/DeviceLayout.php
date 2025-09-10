<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceLayout extends Model
{
    protected $fillable = [
        'layout_name',
        'layout_type',
        'device_id',
        'status'
    ];

    protected $casts = [
        'layout_type' => 'integer',
        'status' => 'integer',
        'device_id' => 'integer'
    ];

    // Layout type constants
    const LAYOUT_TYPE_FULL_SCREEN = 0;
    const LAYOUT_TYPE_SPLIT_SCREEN = 1;
    const LAYOUT_TYPE_THREE_GRID_SCREEN = 2;
    const LAYOUT_TYPE_FOUR_GRID_SCREEN = 3;

    // Status constants
    const STATUS_DELETE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_BLOCK = 3;

    /**
     * Get the device that owns the layout.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Screens under this layout.
     */
    public function screens(): HasMany
    {
        return $this->hasMany(DeviceScreen::class, 'layout_id');
    }

    /**
     * Get layout type name
     */
    public function getLayoutTypeNameAttribute(): string
    {
        return match ($this->layout_type) {
            self::LAYOUT_TYPE_FULL_SCREEN => 'Full Screen',
            self::LAYOUT_TYPE_SPLIT_SCREEN => 'Split Screen',
            self::LAYOUT_TYPE_THREE_GRID_SCREEN => 'Three Grid Screen',
            self::LAYOUT_TYPE_FOUR_GRID_SCREEN => 'Four Grid Screen',
            default => 'Unknown'
        };
    }

    /**
     * Get status name
     */
    public function getStatusNameAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DELETE => 'Delete',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_BLOCK => 'Block',
            default => 'Unknown'
        };
    }

    /**
     * Get maximum allowed screens for this layout type
     */
    public function getMaxScreensAttribute(): int
    {
        return match ($this->layout_type) {
            self::LAYOUT_TYPE_FULL_SCREEN => 1,
            self::LAYOUT_TYPE_SPLIT_SCREEN => 2,
            self::LAYOUT_TYPE_THREE_GRID_SCREEN => 3,
            self::LAYOUT_TYPE_FOUR_GRID_SCREEN => 4,
            default => 1
        };
    }

    /**
     * Check if layout type allows adding more screens
     */
    public function canAddMoreScreens(): bool
    {
        return $this->screens()->count() < $this->max_screens;
    }

    /**
     * Get remaining screen slots available
     */
    public function getRemainingScreenSlotsAttribute(): int
    {
        return max(0, $this->max_screens - $this->screens()->count());
    }
}
