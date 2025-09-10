<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_BLOCKED = 2;
    const STATUS_DELETED = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the status text attribute
     */
    public function getStatusTextAttribute()
    {
        $status = (int) $this->status;
        return match ($status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_BLOCKED => 'Blocked',
            self::STATUS_DELETED => 'Deleted',
            default => 'Unknown'
        };
    }

    /**
     * Get the status badge class attribute
     */
    public function getStatusBadgeClassAttribute()
    {
        $status = (int) $this->status;
        return match ($status) {
            self::STATUS_ACTIVE => 'bg-success',
            self::STATUS_INACTIVE => 'bg-danger',
            self::STATUS_BLOCKED => 'bg-warning',
            self::STATUS_DELETED => 'bg-secondary',
            default => 'bg-secondary'
        };
    }

    /**
     * Get the user who created this location.
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this location.
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the areas associated with this location.
     */
    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'area_locations');
    }
}
