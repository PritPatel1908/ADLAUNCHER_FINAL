<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_DELETE = 0;
    const STATUS_ACTIVATE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_BLOCK = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'unique_id',
        'location_id',
        'company_id',
        'area_id',
        'ip',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Append computed attributes when serializing the model.
     */
    protected $appends = [
        'layouts_count',
        'active_layouts_count',
        'screens_count',
    ];

    /**
     * Company relation.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Location relation.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Area relation.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Creator relation.
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Updater relation.
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Device layouts relation.
     */
    public function deviceLayouts(): HasMany
    {
        return $this->hasMany(DeviceLayout::class);
    }

    /**
     * Device screens relation.
     */
    public function deviceScreens(): HasMany
    {
        return $this->hasMany(DeviceScreen::class);
    }

    /**
     * Get the count of active layouts for this device.
     */
    public function getActiveLayoutsCountAttribute(): int
    {
        return $this->deviceLayouts()->where('status', DeviceLayout::STATUS_ACTIVE)->count();
    }

    /**
     * Get the count of all layouts for this device.
     */
    public function getLayoutsCountAttribute(): int
    {
        return $this->deviceLayouts()->count();
    }

    /**
     * Get the count of all screens for this device.
     */
    public function getScreensCountAttribute(): int
    {
        return $this->deviceScreens()->count();
    }

    /**
     * Get layouts by status.
     */
    public function getLayoutsByStatus(int $status): int
    {
        return $this->deviceLayouts()->where('status', $status)->count();
    }
}
