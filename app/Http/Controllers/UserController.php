<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\User;
use App\Models\Company;
use App\Models\Location;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('user.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id',
            'gender' => 'nullable|integer|in:1,2,3',
            'date_of_birth' => 'nullable|date',
            'date_of_joining' => 'nullable|date',
            'status' => 'required|in:delete,active,deactivate,block',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,id',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'area_ids' => 'nullable|array',
            'area_ids.*' => 'exists:areas,id',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        try {
            DB::beginTransaction();

            // Generate username from first_name and last_name
            $username = User::generateUsername($request->first_name, $request->last_name);

            // Create full name from first_name and last_name
            $fullName = trim($request->first_name . ' ' . $request->last_name);

            $user = User::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'name' => $fullName,
                'username' => $username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile' => $request->mobile,
                'phone' => $request->phone,
                'employee_id' => $request->employee_id,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'date_of_joining' => $request->date_of_joining,
                // Convert status string to integer
                'status' => $request->status === 'delete' ? 0 : ($request->status === 'active' ? 1 : ($request->status === 'deactivate' ? 2 : 3)),
            ]);

            // Attach relationships
            if ($request->company_ids) {
                $user->companies()->attach($request->company_ids);
            }
            if ($request->location_ids) {
                $user->locations()->attach($request->location_ids);
            }
            if ($request->area_ids) {
                $user->areas()->attach($request->area_ids);
            }
            if ($request->role_ids) {
                $user->roles()->attach($request->role_ids);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user->load(['companies', 'locations', 'areas', 'roles'])->append('full_name')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['companies', 'locations', 'areas', 'roles']);

        // Check if request is AJAX
        if (request()->ajax()) {
            return response()->json([
                'user' => $user
            ]);
        }

        return view('user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user->load(['companies', 'locations', 'areas', 'roles']);
        return response()->json([
            'success' => true,
            'user' => $user->append('full_name')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'mobile' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id,' . $user->id,
            'gender' => 'nullable|integer|in:1,2,3',
            'date_of_birth' => 'nullable|date',
            'date_of_joining' => 'nullable|date',
            'status' => 'required|in:delete,active,deactivate,block',
            'company_ids' => 'nullable|array',
            'company_ids.*' => 'exists:companies,id',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'area_ids' => 'nullable|array',
            'area_ids.*' => 'exists:areas,id',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        try {
            DB::beginTransaction();

            // Create full name from first_name and last_name
            $fullName = trim($request->first_name . ' ' . $request->last_name);

            $updateData = [
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'name' => $fullName,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'phone' => $request->phone,
                'employee_id' => $request->employee_id,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'date_of_joining' => $request->date_of_joining,
                // Convert status string to integer
                'status' => $request->status === 'delete' ? 0 : ($request->status === 'active' ? 1 : ($request->status === 'deactivate' ? 2 : 3)),
            ];

            // Update password only if provided
            if ($request->password) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Sync relationships
            $user->companies()->sync($request->company_ids ?? []);
            $user->locations()->sync($request->location_ids ?? []);
            $user->areas()->sync($request->area_ids ?? []);
            $user->roles()->sync($request->role_ids ?? []);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user->load(['companies', 'locations', 'areas', 'roles'])->append('full_name')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users data for DataTables
     */
    public function getData(Request $request)
    {
        $query = User::with(['companies', 'locations', 'areas', 'roles']);

        // Apply filters
        if ($request->filled('first_name_filter')) {
            $query->where('first_name', 'like', '%' . $request->first_name_filter . '%');
        }

        if ($request->filled('last_name_filter')) {
            $query->where('last_name', 'like', '%' . $request->last_name_filter . '%');
        }

        if ($request->filled('email_filter')) {
            $query->where('email', 'like', '%' . $request->email_filter . '%');
        }

        if ($request->filled('mobile_filter')) {
            $query->where('mobile', 'like', '%' . $request->mobile_filter . '%');
        }

        if ($request->filled('employee_id_filter')) {
            $query->where('employee_id', 'like', '%' . $request->employee_id_filter . '%');
        }

        if ($request->filled('status_filter') && is_array($request->status_filter)) {
            $statusValues = array_map(function ($status) {
                return $status === 'active' ? 1 : 0;
            }, $request->status_filter);
            $query->whereIn('status', $statusValues);
        }

        // Apply date range filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date . ' 23:59:59']);
        }

        // Apply sorting
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'name_asc':
                    $query->orderBy('first_name', 'asc')->orderBy('last_name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('first_name', 'desc')->orderBy('last_name', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // DataTables server-side response without external package
        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);

        $recordsTotal = (clone $query)->count();

        // Apply global search if provided by DataTables
        $searchValue = $request->input('search.value');
        if ($searchValue) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('first_name', 'like', "%$searchValue%")
                    ->orWhere('last_name', 'like', "%$searchValue%")
                    ->orWhere('email', 'like', "%$searchValue%")
                    ->orWhere('mobile', 'like', "%$searchValue%")
                    ->orWhere('employee_id', 'like', "%$searchValue%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        $users = $query
            ->skip($start)
            ->take($length)
            ->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'phone' => $user->phone,
                'employee_id' => $user->employee_id,
                'gender' => $user->gender,
                'date_of_birth' => $user->date_of_birth,
                'date_of_joining' => $user->date_of_joining,
                'companies_count' => $user->companies->count(),
                'locations_count' => $user->locations->count(),
                'areas_count' => $user->areas->count(),
                'roles_count' => $user->roles->count(),
                'roles' => $user->roles->pluck('role_name')->toArray(),
                'is_admin' => (bool) ($user->is_admin ?? false),
                'is_client' => (bool) ($user->is_client ?? false),
                'is_user' => (bool) ($user->is_user ?? true),
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'status' => $user->status,
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Get all available roles for role assignment
     */
    public function getRoles()
    {
        try {
            $roles = Role::select('id', 'role_name')
                ->orderBy('role_name')
                ->get();

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
     * Assign roles to a user
     */
    public function assignRoles(Request $request, User $user)
    {
        try {
            $request->validate([
                'role_ids' => 'required|array',
                'role_ids.*' => 'exists:roles,id',
            ]);

            $user->roles()->sync($request->role_ids);

            return response()->json([
                'success' => true,
                'message' => 'Roles assigned successfully',
                'user' => $user->load('roles')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error assigning roles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove roles from a user
     */
    public function removeRoles(Request $request, User $user)
    {
        try {
            $request->validate([
                'role_ids' => 'required|array',
                'role_ids.*' => 'exists:roles,id',
            ]);

            $user->roles()->detach($request->role_ids);

            return response()->json([
                'success' => true,
                'message' => 'Roles removed successfully',
                'user' => $user->load('roles')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing roles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's current roles
     */
    public function getUserRoles(User $user)
    {
        try {
            $roles = $user->roles()->select('id', 'role_name')->get();

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading user roles: ' . $e->getMessage()
            ], 500);
        }
    }
}
