<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Area extends Model
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
        'description',
        'code',
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
     * Get the status text attribute
     */
    public function getStatusTextAttribute()
    {
        $status = (int) $this->status;
        return match ($status) {
            self::STATUS_ACTIVATE => 'Activate',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_BLOCK => 'Block',
            self::STATUS_DELETE => 'Delete',
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
            self::STATUS_ACTIVATE => 'bg-success',
            self::STATUS_INACTIVE => 'bg-warning',
            self::STATUS_BLOCK => 'bg-danger',
            self::STATUS_DELETE => 'bg-secondary',
            default => 'bg-secondary'
        };
    }

    /**
     * Get the user who created this area.
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this area.
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the locations associated with this area.
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'area_locations');
    }

    /**
     * Get the companies associated with this area.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'area_companies');
    }
}
