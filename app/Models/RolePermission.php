<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermission extends Model
{
    protected $fillable = [
        'role_id',
        'modules',
        'view',
        'create',
        'edit',
        'delete',
        'import',
        'export',
        'manage_columns',
    ];

    protected $casts = [
        'view' => 'boolean',
        'create' => 'boolean',
        'edit' => 'boolean',
        'delete' => 'boolean',
        'import' => 'boolean',
        'export' => 'boolean',
        'manage_columns' => 'boolean',
    ];

    /**
     * Get the role that owns the role permission.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
