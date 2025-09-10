<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'username',
        'employee_id',
        'name',
        'gender',
        'phone',
        'mobile',
        'date_of_birth',
        'date_of_joining',
        'email',
        'password',
        'is_admin',
        'is_client',
        'is_user',
        'is_login',
        'last_ip_address',
        'last_url',
        'last_login_at',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'full_name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'date_of_joining' => 'date',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_client' => 'boolean',
            'is_user' => 'boolean',
            'is_login' => 'boolean',
            'gender' => 'integer',
            'status' => 'integer',
        ];
    }

    /**
     * Get the column preferences for the user.
     */
    public function showColumns(): HasMany
    {
        return $this->hasMany(ShowColumn::class);
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * Get the companies associated with the user.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'user_companies');
    }

    /**
     * Get the locations associated with the user.
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'user_locations');
    }

    /**
     * Get the areas associated with the user.
     */
    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'user_areas');
    }

    /**
     * Generate username from first_name and last_name
     */
    public static function generateUsername($firstName, $lastName)
    {
        $baseUsername = strtolower($firstName . $lastName);
        $username = $baseUsername;
        $counter = 1;

        while (self::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute()
    {
        $name = $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        $name .= ' ' . $this->last_name;
        return $name;
    }

    /**
     * Check if user has permission for a specific module and action
     */
    public function hasPermission(string $module, string $action = 'view'): bool
    {
        // If user is admin, allow all permissions
        if ($this->is_admin) {
            return true;
        }

        // Get user's roles with their permissions
        $userRoles = $this->roles()->with('rolePermissions')->get();

        foreach ($userRoles as $role) {
            foreach ($role->rolePermissions as $permission) {
                // Case insensitive comparison for module names
                if (strtolower($permission->modules) === strtolower($module) && $permission->$action) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user can view a module
     */
    public function canView(string $module): bool
    {
        return $this->hasPermission($module, 'view');
    }

    /**
     * Check if user can create in a module
     */
    public function canCreate(string $module): bool
    {
        return $this->hasPermission($module, 'create');
    }

    /**
     * Check if user can edit in a module
     */
    public function canEdit(string $module): bool
    {
        return $this->hasPermission($module, 'edit');
    }

    /**
     * Check if user can delete in a module
     */
    public function canDelete(string $module): bool
    {
        return $this->hasPermission($module, 'delete');
    }

    /**
     * Check if user can import in a module
     */
    public function canImport(string $module): bool
    {
        return $this->hasPermission($module, 'import');
    }

    /**
     * Check if user can export in a module
     */
    public function canExport(string $module): bool
    {
        return $this->hasPermission($module, 'export');
    }

    /**
     * Check if user can manage columns in a module
     */
    public function canManageColumns(string $module): bool
    {
        return $this->hasPermission($module, 'manage_columns');
    }

    /**
     * Get all permissions for a specific module
     */
    public function getModulePermissions(string $module): array
    {
        $permissions = [
            'view' => false,
            'create' => false,
            'edit' => false,
            'delete' => false,
            'import' => false,
            'export' => false,
            'manage_columns' => false,
        ];

        // If user is admin, allow all permissions
        if ($this->is_admin) {
            return array_fill_keys(array_keys($permissions), true);
        }

        // Get user's roles with their permissions
        $userRoles = $this->roles()->with('rolePermissions')->get();

        foreach ($userRoles as $role) {
            foreach ($role->rolePermissions as $permission) {
                // Case insensitive comparison for module names
                if (strtolower($permission->modules) === strtolower($module)) {
                    $permissions['view'] = $permissions['view'] || $permission->view;
                    $permissions['create'] = $permissions['create'] || $permission->create;
                    $permissions['edit'] = $permissions['edit'] || $permission->edit;
                    $permissions['delete'] = $permissions['delete'] || $permission->delete;
                    $permissions['import'] = $permissions['import'] || $permission->import;
                    $permissions['export'] = $permissions['export'] || $permission->export;
                    $permissions['manage_columns'] = $permissions['manage_columns'] || $permission->manage_columns;
                }
            }
        }

        return $permissions;
    }

    /**
     * Get all modules that user has access to (at least view permission)
     */
    public function getAccessibleModules(): array
    {
        $accessibleModules = [];

        // If user is admin, return all available modules
        if ($this->is_admin) {
            return [
                'user',
                'company',
                'location',
                'area',
                'device',
                'schedule',
                'role-permission'
            ];
        }

        // Get user's roles with their permissions
        $userRoles = $this->roles()->with('rolePermissions')->get();

        foreach ($userRoles as $role) {
            foreach ($role->rolePermissions as $permission) {
                if ($permission->view && !in_array(strtolower($permission->modules), $accessibleModules)) {
                    $accessibleModules[] = strtolower($permission->modules);
                }
            }
        }

        return $accessibleModules;
    }

    /**
     * Check if user has access to any module
     */
    public function hasAnyModuleAccess(): bool
    {
        return count($this->getAccessibleModules()) > 0;
    }
}
