<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        return view('role-permission.index');
    }

    /**
     * Display the permission management page.
     */
    public function permissionIndex()
    {
        return view('role-permission.permission-index');
    }

    /**
     * Get roles data for AJAX requests.
     */
    public function getRolesData(): JsonResponse
    {
        try {
            $roles = Role::withCount('rolePermissions')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($role) {
                    // Count total individual permissions (view, create, edit, delete, import, export, manage_columns)
                    $total_individual_permissions = 0;
                    $granted_individual_permissions = 0;

                    $rolePermissions = $role->rolePermissions;
                    foreach ($rolePermissions as $permission) {
                        // Count all 7 individual permission types
                        $permissionTypes = ['view', 'create', 'edit', 'delete', 'import', 'export', 'manage_columns'];
                        foreach ($permissionTypes as $type) {
                            $total_individual_permissions++;
                            if ($permission->$type) {
                                $granted_individual_permissions++;
                            }
                        }
                    }

                    return [
                        'id' => $role->id,
                        'role_name' => $role->role_name,
                        'users_count' => $role->users()->count() ?? 0,
                        'permissions_count' => $granted_individual_permissions,
                        'total_permissions' => $total_individual_permissions,
                        'created_at' => $role->created_at->format('d M Y, h:i A'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading roles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'role_name' => 'required|string|max:255|unique:roles,role_name',
                'role_description' => 'nullable|string|max:500',
            ]);

            $role = Role::create([
                'role_name' => $validated['role_name'],
                'description' => $validated['role_description'] ?? null,
            ]);

            // Auto-create permissions for all modules based on view folders
            $this->createDefaultPermissions($role);

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $role->id,
                    'role_name' => $role->role_name,
                    'description' => $role->description,
                    'created_at' => $role->created_at->format('d M Y, h:i A'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        try {
            $validated = $request->validate([
                'role_name' => 'required|string|max:255|unique:roles,role_name,' . $role->id,
                'role_description' => 'nullable|string|max:500',
            ]);

            $role->update([
                'role_name' => $validated['role_name'],
                'description' => $validated['role_description'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $role
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): JsonResponse
    {
        try {
            // Check if role has users assigned
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role. Users are still assigned to this role.'
                ], 400);
            }

            // Delete role permissions first
            $role->rolePermissions()->delete();

            // Delete the role
            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permissions for a specific role.
     */
    public function getPermissions(Role $role): JsonResponse
    {
        try {
            // Get all available modules
            $modules = $this->getAvailableModules();

            // Get existing permissions for the role
            $existingPermissions = $role->rolePermissions()->get()->keyBy('modules');

            // Build permissions data
            $permissionsData = [];
            foreach ($modules as $module => $subModules) {
                foreach ($subModules as $subModule) {
                    $permissionsData[] = [
                        'module' => $module,
                        'sub_module' => $subModule,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $permissionsData,
                'permissions' => $existingPermissions->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store permissions for a specific role.
     */
    public function storePermissions(Request $request, Role $role): JsonResponse
    {
        try {
            // Log the incoming request data for debugging
            Log::info('Permission save request data:', $request->all());
            Log::info('Role ID:', ['role_id' => $role->id]);

            // Handle JSON requests
            if ($request->isJson()) {
                $data = $request->json()->all();
                Log::info('JSON data parsed:', $data);
                $request->merge($data);
            }

            $validated = $request->validate([
                'permissions' => 'required|array',
                'permissions.*.modules' => 'required|string',
                'permissions.*.view' => 'sometimes|boolean',
                'permissions.*.create' => 'sometimes|boolean',
                'permissions.*.edit' => 'sometimes|boolean',
                'permissions.*.delete' => 'sometimes|boolean',
                'permissions.*.import' => 'sometimes|boolean',
                'permissions.*.export' => 'sometimes|boolean',
                'permissions.*.manage_columns' => 'sometimes|boolean',
            ]);

            // Log the validated data
            Log::info('Validated permission data:', $validated);

            // Update existing permissions instead of delete+create
            foreach ($validated['permissions'] as $permissionData) {
                $view = $permissionData['view'] ?? false;
                $create = $permissionData['create'] ?? false;
                $edit = $permissionData['edit'] ?? false;
                $delete = $permissionData['delete'] ?? false;
                $import = $permissionData['import'] ?? false;
                $export = $permissionData['export'] ?? false;
                $manageColumns = $permissionData['manage_columns'] ?? false;

                // Check if permission exists for this module
                $existingPermission = RolePermission::where('role_id', $role->id)
                    ->where('modules', $permissionData['modules'])
                    ->first();

                if ($existingPermission) {
                    // Always update existing permission (don't delete even if all false)
                    $existingPermission->update([
                        'view' => $view,
                        'create' => $create,
                        'edit' => $edit,
                        'delete' => $delete,
                        'import' => $import,
                        'export' => $export,
                        'manage_columns' => $manageColumns,
                    ]);
                    Log::info('Updated permission:', $existingPermission->toArray());
                } else {
                    // Create new permission (this should not happen as all permissions are created by default)
                    $newPermission = RolePermission::create([
                        'role_id' => $role->id,
                        'modules' => $permissionData['modules'],
                        'view' => $view,
                        'create' => $create,
                        'edit' => $edit,
                        'delete' => $delete,
                        'import' => $import,
                        'export' => $export,
                        'manage_columns' => $manageColumns,
                    ]);
                    Log::info('Created new permission (unexpected):', $newPermission->toArray());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Permissions saved successfully'
            ]);
        } catch (ValidationException $e) {
            Log::error('Permission validation failed:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available modules and sub-modules based on view folders.
     */
    private function getAvailableModules(): array
    {
        $modules = [];
        $viewsPath = resource_path('views');

        // Get all directories in views folder except Layout, Dashboard, auth, and extra
        $directories = array_diff(scandir($viewsPath), ['.', '..', 'Layout', 'Dashboard', 'auth', 'extra']);

        foreach ($directories as $directory) {
            if (is_dir($viewsPath . '/' . $directory)) {
                $moduleName = ucfirst(str_replace('-', ' ', $directory));
                $modules[$moduleName] = [$moduleName];
            }
        }

        return $modules;
    }

    /**
     * Create default permissions for a new role.
     * Creates permissions for all modules with all false values initially.
     */
    private function createDefaultPermissions(Role $role): void
    {
        $modules = $this->getAvailableModules();

        foreach ($modules as $moduleName => $subModules) {
            foreach ($subModules as $subModule) {
                RolePermission::create([
                    'role_id' => $role->id,
                    'modules' => $subModule,
                    'view' => false,
                    'create' => false,
                    'edit' => false,
                    'delete' => false,
                    'import' => false,
                    'export' => false,
                    'manage_columns' => false,
                ]);
            }
        }

        Log::info('Default permissions created for role:', ['role_id' => $role->id, 'role_name' => $role->role_name]);
    }

    /**
     * Get role statistics.
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'total_roles' => Role::count(),
                'total_permissions' => RolePermission::count(),
                'roles_with_users' => Role::has('users')->count(),
                'recent_roles' => Role::orderBy('created_at', 'desc')->limit(5)->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
