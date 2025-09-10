<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Check if current user has permission for a specific module and action
     */
    public static function hasPermission(string $module, string $action = 'view'): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasPermission($module, $action);
    }

    /**
     * Check if current user can view a module
     */
    public static function canView(string $module): bool
    {
        return self::hasPermission($module, 'view');
    }

    /**
     * Check if current user can create in a module
     */
    public static function canCreate(string $module): bool
    {
        return self::hasPermission($module, 'create');
    }

    /**
     * Check if current user can edit in a module
     */
    public static function canEdit(string $module): bool
    {
        return self::hasPermission($module, 'edit');
    }

    /**
     * Check if current user can delete in a module
     */
    public static function canDelete(string $module): bool
    {
        return self::hasPermission($module, 'delete');
    }

    /**
     * Check if current user can import in a module
     */
    public static function canImport(string $module): bool
    {
        return self::hasPermission($module, 'import');
    }

    /**
     * Check if current user can export in a module
     */
    public static function canExport(string $module): bool
    {
        return self::hasPermission($module, 'export');
    }

    /**
     * Check if current user can manage columns in a module
     */
    public static function canManageColumns(string $module): bool
    {
        return self::hasPermission($module, 'manage_columns');
    }

    /**
     * Get all permissions for current user for a specific module
     */
    public static function getModulePermissions(string $module): array
    {
        $user = Auth::user();
        if (!$user) {
            return [
                'view' => false,
                'create' => false,
                'edit' => false,
                'delete' => false,
                'import' => false,
                'export' => false,
                'manage_columns' => false,
            ];
        }

        return $user->getModulePermissions($module);
    }

    /**
     * Get all modules that current user has access to
     */
    public static function getAccessibleModules(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        return $user->getAccessibleModules();
    }

    /**
     * Check if current user has access to any module
     */
    public static function hasAnyModuleAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasAnyModuleAccess();
    }

    /**
     * Check if current user can view audit fields (created_by, updated_by)
     * Only admin users can view these fields
     */
    // public static function canViewAuditFields(): bool
    // {
    //     $user = Auth::user();
    //     if (!$user) {
    //         return false;
    //     }

    //     // Only admin users can view audit fields
    //     return $user->is_admin;
    // }
}
