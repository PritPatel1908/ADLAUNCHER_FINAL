<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\DeviceApiController;
use App\Http\Controllers\ShowColumnController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceLayoutController;
use App\Http\Controllers\DeviceScreenController;
use App\Http\Controllers\ScheduleMediaController;
use App\Http\Controllers\RolePermissionController;

// Test route for location permissions (remove in production)
Route::get('test-location-permissions', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated'], 401);
    }

    return response()->json([
        'user_id' => $user->id,
        'user_name' => $user->full_name,
        'is_admin' => $user->is_admin,
        'can_view_location' => $user->canView('location'),
        'can_edit_location' => $user->canEdit('location'),
        'can_create_location' => $user->canCreate('location'),
        'can_delete_location' => $user->canDelete('location'),
        'accessible_modules' => $user->getAccessibleModules(),
        'location_permissions' => $user->getModulePermissions('location')
    ]);
})->middleware('auth')->name('test.location.permissions');

// Debug route for permissions (remove in production)
Route::get('debug-permissions', function () {
    $user = auth()->user();
    if (!$user) {
        return 'Please login first';
    }

    $data = [
        'user' => $user,
        'is_admin' => $user->is_admin,
        'roles' => $user->roles,
        'accessible_modules' => $user->getAccessibleModules(),
        'has_any_access' => $user->hasAnyModuleAccess(),
    ];

    // Check each module
    $modules = ['user', 'company', 'location', 'area', 'device', 'schedule', 'role-permission'];
    foreach ($modules as $module) {
        $data['module_permissions'][$module] = [
            'can_view' => $user->canView($module),
            'can_create' => $user->canCreate($module),
            'can_edit' => $user->canEdit($module),
            'can_delete' => $user->canDelete($module),
            'permissions' => $user->getModulePermissions($module)
        ];
    }

    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
})->middleware('auth')->name('debug.permissions');

// Authentication Routes
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.submit');
Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register.submit');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard Route
Route::get('', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth');

// Location Routes with individual permissions
Route::get('location', [LocationController::class, 'index'])->name('location.index')->middleware(['auth', 'permission:location,view']);
Route::get('location/create', [LocationController::class, 'create'])->name('location.create')->middleware(['auth', 'permission:location,create']);
Route::post('location', [LocationController::class, 'store'])->name('location.store')->middleware(['auth', 'permission:location,create']);
Route::get('location/{location}', [LocationController::class, 'show'])->name('location.show')->middleware(['auth', 'permission:location,view']);
Route::get('location/{location}/edit', [LocationController::class, 'edit'])->name('location.edit')->middleware(['auth', 'permission:location,edit']);
Route::put('location/{location}', [LocationController::class, 'update'])->name('location.update')->middleware(['auth', 'permission:location,edit']);
Route::delete('location/{location}', [LocationController::class, 'destroy'])->name('location.destroy')->middleware(['auth', 'permission:location,delete']);
Route::get('locations/data', [LocationController::class, 'getData'])->name('locations.data')->middleware(['auth', 'permission:location,view']);

// Company Routes with individual permissions
Route::get('company', [CompanyController::class, 'index'])->name('company.index')->middleware(['auth', 'permission:company,view']);
Route::get('company/create', [CompanyController::class, 'create'])->name('company.create')->middleware(['auth', 'permission:company,create']);
Route::post('company', [CompanyController::class, 'store'])->name('company.store')->middleware(['auth', 'permission:company,create']);
Route::get('company/{company}', [CompanyController::class, 'show'])->name('company.show')->middleware(['auth', 'permission:company,view']);
Route::get('company/{company}/edit', [CompanyController::class, 'edit'])->name('company.edit')->middleware(['auth', 'permission:company,edit']);
Route::put('company/{company}', [CompanyController::class, 'update'])->name('company.update')->middleware(['auth', 'permission:company,edit']);
Route::delete('company/{company}', [CompanyController::class, 'destroy'])->name('company.destroy')->middleware(['auth', 'permission:company,delete']);
Route::get('companies/data', [CompanyController::class, 'getData'])->name('companies.data')->middleware(['auth', 'permission:company,view']);

// Area Routes with individual permissions
Route::get('area', [AreaController::class, 'index'])->name('area.index')->middleware(['auth', 'permission:area,view']);
Route::get('area/create', [AreaController::class, 'create'])->name('area.create')->middleware(['auth', 'permission:area,create']);
Route::post('area', [AreaController::class, 'store'])->name('area.store')->middleware(['auth', 'permission:area,create']);
Route::get('area/{area}', [AreaController::class, 'show'])->name('area.show')->middleware(['auth', 'permission:area,view']);
Route::get('area/{area}/edit', [AreaController::class, 'edit'])->name('area.edit')->middleware(['auth', 'permission:area,edit']);
Route::put('area/{area}', [AreaController::class, 'update'])->name('area.update')->middleware(['auth', 'permission:area,edit']);
Route::delete('area/{area}', [AreaController::class, 'destroy'])->name('area.destroy')->middleware(['auth', 'permission:area,delete']);
Route::get('areas/data', [AreaController::class, 'getData'])->name('areas.data')->middleware(['auth', 'permission:area,view']);

// User Routes with individual permissions
Route::get('user', [UserController::class, 'index'])->name('user.index')->middleware(['auth', 'permission:user,view']);
Route::get('user/create', [UserController::class, 'create'])->name('user.create')->middleware(['auth', 'permission:user,create']);
Route::post('user', [UserController::class, 'store'])->name('user.store')->middleware(['auth', 'permission:user,create']);
Route::get('user/{user}', [UserController::class, 'show'])->name('user.show')->middleware(['auth', 'permission:user,view']);
Route::get('user/{user}/edit', [UserController::class, 'edit'])->name('user.edit')->middleware(['auth', 'permission:user,edit']);
Route::put('user/{user}', [UserController::class, 'update'])->name('user.update')->middleware(['auth', 'permission:user,edit']);
Route::delete('user/{user}', [UserController::class, 'destroy'])->name('user.destroy')->middleware(['auth', 'permission:user,delete']);
Route::get('users/data', [UserController::class, 'getData'])->name('users.data')->middleware(['auth', 'permission:user,view']);
Route::get('users/roles', [UserController::class, 'getRoles'])->name('users.roles')->middleware(['auth', 'permission:user,view']);
Route::get('users/{user}/roles', [UserController::class, 'getUserRoles'])->name('users.user-roles')->middleware(['auth', 'permission:user,view']);
Route::post('users/{user}/assign-roles', [UserController::class, 'assignRoles'])->name('users.assign-roles')->middleware(['auth', 'permission:user,edit']);
Route::post('users/{user}/remove-roles', [UserController::class, 'removeRoles'])->name('users.remove-roles')->middleware(['auth', 'permission:user,edit']);

// Device Routes with individual permissions
Route::get('device', [DeviceController::class, 'index'])->name('device.index')->middleware(['auth', 'permission:device,view']);
Route::get('device/create', [DeviceController::class, 'create'])->name('device.create')->middleware(['auth', 'permission:device,create']);
Route::post('device', [DeviceController::class, 'store'])->name('device.store')->middleware(['auth', 'permission:device,create']);
Route::get('device/{device}', [DeviceController::class, 'show'])->name('device.show')->middleware(['auth', 'permission:device,view']);
Route::get('device/{device}/edit', [DeviceController::class, 'edit'])->name('device.edit')->middleware(['auth', 'permission:device,edit']);
Route::put('device/{device}', [DeviceController::class, 'update'])->name('device.update')->middleware(['auth', 'permission:device,edit']);
Route::delete('device/{device}', [DeviceController::class, 'destroy'])->name('device.destroy')->middleware(['auth', 'permission:device,delete']);
Route::get('devices/data', [DeviceController::class, 'getData'])->name('devices.data')->middleware(['auth', 'permission:device,view']);

// Device Layout Routes
Route::resource('device-layout', DeviceLayoutController::class)->middleware('auth');
Route::get('device-layouts', [DeviceLayoutController::class, 'index'])->name('device-layouts.index')->middleware('auth');
Route::get('device/{device}/layouts', [DeviceLayoutController::class, 'getDeviceLayouts'])->name('device.layouts')->middleware('auth');
Route::get('device-layout-stats', [DeviceLayoutController::class, 'getLayoutStats'])->name('device-layout.stats')->middleware('auth');

// Device Screen Routes
Route::resource('device-screen', DeviceScreenController::class)->middleware('auth');
Route::get('device/{device}/screens', [DeviceScreenController::class, 'getDeviceScreens'])->name('device.screens')->middleware('auth');
Route::get('layout/{layout}/screens', [DeviceScreenController::class, 'getLayoutScreens'])->name('layout.screens')->middleware('auth');
Route::post('device-screen/validate', [DeviceScreenController::class, 'validateScreenConfiguration'])->name('device-screen.validate')->middleware('auth');
Route::get('layout/{layout}/validation-rules', [DeviceScreenController::class, 'getLayoutValidationRules'])->name('layout.validation-rules')->middleware('auth');

// Schedule Routes with individual permissions
Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule.index')->middleware(['auth', 'permission:schedule,view']);
Route::get('schedule/create', [ScheduleController::class, 'create'])->name('schedule.create')->middleware(['auth', 'permission:schedule,create']);
Route::post('schedule', [ScheduleController::class, 'store'])->name('schedule.store')->middleware(['auth', 'permission:schedule,create']);
Route::get('schedule/{schedule}', [ScheduleController::class, 'show'])->name('schedule.show')->middleware(['auth', 'permission:schedule,view']);
Route::get('schedule/{schedule}/edit', [ScheduleController::class, 'edit'])->name('schedule.edit')->middleware(['auth', 'permission:schedule,edit']);
Route::put('schedule/{schedule}', [ScheduleController::class, 'update'])->name('schedule.update')->middleware(['auth', 'permission:schedule,edit']);
Route::delete('schedule/{schedule}', [ScheduleController::class, 'destroy'])->name('schedule.destroy')->middleware(['auth', 'permission:schedule,delete']);
Route::get('schedules/data', [ScheduleController::class, 'getData'])->name('schedules.data')->middleware(['auth', 'permission:schedule,view']);
// Schedule Media Routes
Route::resource('schedule-media', ScheduleMediaController::class)->middleware('auth');
Route::get('schedule/{schedule}/medias', [ScheduleMediaController::class, 'getScheduleMedias'])->name('schedule.medias')->middleware('auth');

// Chunked Upload Routes for Large Video Files
Route::post('schedule/chunked-upload', [ScheduleController::class, 'chunkedUpload'])->name('schedule.chunked-upload')->middleware(['auth', 'permission:schedule,create']);

// Column Visibility Routes
Route::get('columns', [ShowColumnController::class, 'getColumns'])->name('columns.get')->middleware('auth');
Route::post('columns', [ShowColumnController::class, 'updateColumn'])->name('columns.update')->middleware('auth');

// Role & Permission Routes with individual permissions
Route::get('role-permission', [RolePermissionController::class, 'index'])->name('role-permission.index')->middleware(['auth', 'permission:role-permission,view']);
Route::get('permissions', [RolePermissionController::class, 'permissionIndex'])->name('permissions.index')->middleware(['auth', 'permission:role-permission,view']);
Route::get('roles/data', [RolePermissionController::class, 'getRolesData'])->name('roles.data')->middleware(['auth', 'permission:role-permission,view']);
Route::get('roles', [RolePermissionController::class, 'index'])->name('roles.index')->middleware(['auth', 'permission:role-permission,view']);
Route::get('roles/create', [RolePermissionController::class, 'create'])->name('roles.create')->middleware(['auth', 'permission:role-permission,create']);
Route::post('roles', [RolePermissionController::class, 'store'])->name('roles.store')->middleware(['auth', 'permission:role-permission,create']);
Route::get('roles/{role}', [RolePermissionController::class, 'show'])->name('roles.show')->middleware(['auth', 'permission:role-permission,view']);
Route::get('roles/{role}/edit', [RolePermissionController::class, 'edit'])->name('roles.edit')->middleware(['auth', 'permission:role-permission,edit']);
Route::put('roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update')->middleware(['auth', 'permission:role-permission,edit']);
Route::delete('roles/{role}', [RolePermissionController::class, 'destroy'])->name('roles.destroy')->middleware(['auth', 'permission:role-permission,delete']);
Route::get('roles/{role}/permissions', [RolePermissionController::class, 'getPermissions'])->name('roles.permissions')->middleware(['auth', 'permission:role-permission,view']);
Route::post('roles/{role}/permissions', [RolePermissionController::class, 'storePermissions'])->name('roles.permissions.store')->middleware(['auth', 'permission:role-permission,edit']);
Route::get('role-permission/stats', [RolePermissionController::class, 'getStats'])->name('role-permission.stats')->middleware(['auth', 'permission:role-permission,view']);

// Device API Routes with rate limiting
Route::post('api/device/get_auth', [DeviceApiController::class, 'getAuth'])
    ->middleware('device.api.rate.limit')
    ->name('api.device.get_auth');
Route::get('api/device/get_auth', [DeviceApiController::class, 'getAuth'])
    ->middleware('device.api.rate.limit')
    ->name('api.device.get_auth.get');
Route::post('api/device/get_new_data', [DeviceApiController::class, 'getNewData'])
    ->middleware('device.api.rate.limit')
    ->name('api.device.get_new_data');
Route::get('api/device/get_new_data', [DeviceApiController::class, 'getNewData'])
    ->middleware('device.api.rate.limit')
    ->name('api.device.get_new_data.get');
Route::get('api/device/status', [DeviceApiController::class, 'getApiStatus'])->name('api.device.status');
// Download media with signed URL for devices
Route::get('api/device/download', [DeviceApiController::class, 'downloadMedia'])
    ->middleware('device.api.rate.limit')
    ->name('api.device.download');
