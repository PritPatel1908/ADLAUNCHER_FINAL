<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'industry',
        'website',
        'email',
        'phone',
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
        // Store 0-3 per migration comment; do not coerce to boolean
        'status' => 'integer',
    ];

    /**
     * Get the user who created this company.
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this company.
     */
    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the locations associated with this company.
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'company_locations');
    }

    /**
     * Get the addresses for this company.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CompanyAddress::class);
    }

    /**
     * Get the contacts for this company.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the notes for this company.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(CompanyNote::class);
    }

    /**
     * Get the areas associated with this company.
     */
    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'area_companies');
    }

    /**
     * Get the users associated with this company.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_companies');
    }
}
