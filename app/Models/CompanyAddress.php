<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyAddress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'type',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
    ];

    /**
     * Get the company that owns this address.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
