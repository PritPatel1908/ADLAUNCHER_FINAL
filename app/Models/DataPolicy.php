<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DataPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_name',
        'self_only',
        'allow_all_location',
        'allow_all_company',
        'allow_all_area',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'self_only' => 'boolean',
        'allow_all_location' => 'boolean',
        'allow_all_company' => 'boolean',
        'allow_all_area' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'data_policy_locations');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'data_policy_companies');
    }

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'data_policy_areas');
    }
}
