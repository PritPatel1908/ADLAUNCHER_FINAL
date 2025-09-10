<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShowColumn extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'table',
        'column_name',
        'is_show',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_show' => 'boolean',
    ];

    /**
     * Get the user that owns the column preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
