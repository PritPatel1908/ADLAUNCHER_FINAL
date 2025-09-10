@extends('layout.main')

@section('meta')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('css')
@endpush

@section('content')
    <!-- Start Content -->
    <div class="content pb-0">
        <div class="container-fluid">
            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">User Details</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Users</a></li>
                            <li class="breadcrumb-item active" aria-current="page">User Details</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    @if (\App\Helpers\PermissionHelper::canExport('user'))
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-outline-primary px-2 shadow"
                                data-bs-toggle="dropdown"><i class="ti ti-package-export me-2"></i>Export</a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <ul>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                class="ti ti-file-type-pdf me-1"></i>Export as PDF</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                class="ti ti-file-type-xls me-1"></i>Export as Excel</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                    <a href="javascript:void(0);" class="btn btn-icon btn-outline-info shadow" data-bs-toggle="tooltip"
                        data-bs-placement="top" aria-label="Refresh" data-bs-original-title="Refresh"><i
                            class="ti ti-refresh"></i></a>
                    <a href="javascript:void(0);" class="btn btn-icon btn-outline-warning shadow" data-bs-toggle="tooltip"
                        data-bs-placement="top" aria-label="Collapse" data-bs-original-title="Collapse"
                        id="collapse-header"><i class="ti ti-transition-top"></i></a>
                </div>
            </div>
            <!-- End Page Header -->

            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <a href="{{ route('user.index') }}"><i class="ti ti-arrow-narrow-left me-1"></i>Back to
                            Users</a>
                    </div>

                    <!-- User Header Card -->
                    <div class="card">
                        <div class="card-body pb-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-xxl avatar-rounded me-3 flex-shrink-0 bg-primary">
                                        <span class="avatar-text">{{ substr($user->first_name, 0, 1) }}</span>
                                        <span class="status {{ $user->status == 1 ? 'online' : 'offline' }}"></span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">{{ $user->full_name }}</h5>
                                        <p class="mb-2">{{ $user->username }}</p>
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            @php
                                                $statusMap = [
                                                    0 => [
                                                        'text' => 'Delete',
                                                        'class' => 'badge-soft-secondary',
                                                        'icon' => 'ti-trash',
                                                    ],
                                                    1 => [
                                                        'text' => 'Active',
                                                        'class' => 'badge-soft-success',
                                                        'icon' => 'ti-check',
                                                    ],
                                                    2 => [
                                                        'text' => 'Deactivate',
                                                        'class' => 'badge-soft-warning',
                                                        'icon' => 'ti-player-pause',
                                                    ],
                                                    3 => [
                                                        'text' => 'Block',
                                                        'class' => 'badge-soft-danger',
                                                        'icon' => 'ti-lock',
                                                    ],
                                                ];
                                                $s = $statusMap[$user->status] ?? [
                                                    'text' => 'Unknown',
                                                    'class' => 'badge-soft-secondary',
                                                    'icon' => 'ti-circle',
                                                ];
                                            @endphp
                                            <span id="header-status-badge" class="badge {{ $s['class'] }} border-0 me-2">
                                                <i class="ti {{ $s['icon'] }} me-1"></i>
                                                {{ $s['text'] }}
                                            </span>
                                            @if ($user->email)
                                                <p class="d-inline-flex align-items-center mb-0 me-3">
                                                    <i class="ti ti-mail text-info me-1"></i> {{ $user->email }}
                                                </p>
                                            @endif
                                            @if ($user->mobile)
                                                <p class="d-inline-flex align-items-center mb-0 me-3">
                                                    <i class="ti ti-phone text-success me-1"></i> {{ $user->mobile }}
                                                </p>
                                            @endif
                                            @if ($user->employee_id)
                                                <p class="d-inline-flex align-items-center mb-0">
                                                    <i class="ti ti-id text-warning me-1"></i> {{ $user->employee_id }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    @if (\App\Helpers\PermissionHelper::canEdit('user'))
                                        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas"
                                            data-bs-target="#offcanvas_edit">
                                            <i class="ti ti-edit me-1"></i>Edit User
                                        </a>
                                    @endif
                                    @if (\App\Helpers\PermissionHelper::canDelete('user'))
                                        <form action="{{ route('user.destroy', $user->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="ti ti-trash me-1"></i>Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Information Cards -->
            <div class="row">
                <!-- User Overview -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">User Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Full Name</h6>
                                        <p>{{ $user->full_name }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Email Address</h6>
                                        <p><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Username</h6>
                                        <p>{{ $user->username }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Employee ID</h6>
                                        <p>{{ $user->employee_id ?? 'N/A' }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Mobile</h6>
                                        <p>{{ $user->mobile ?? 'N/A' }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Status</h6>
                                        @php
                                            $s2 = $statusMap[$user->status] ?? [
                                                'text' => 'Unknown',
                                                'class' => 'badge-soft-secondary',
                                            ];
                                        @endphp
                                        <p><span id="overview-status-badge"
                                                class="badge {{ $s2['class'] }}">{{ $s2['text'] }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Statistics -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">User Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Companies</h6>
                                        <p class="h4 text-primary">{{ $user->companies->count() }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Locations</h6>
                                        <p class="h4 text-success">{{ $user->locations->count() }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Areas</h6>
                                        <p class="h4 text-warning">{{ $user->areas->count() }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Roles</h6>
                                        <p class="h4 text-info">{{ $user->roles->count() }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Last Login</h6>
                                        <p class="h4 text-info">
                                            {{ $user->last_login_at ? $user->last_login_at->format('d M Y') : 'Never' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6 class="fw-semibold">Created</h6>
                                <p class="text-muted">{{ $user->created_at->format('d M Y, h:i A') }}</p>
                                @if ($user->updated_at)
                                    <h6 class="fw-semibold">Last Updated</h6>
                                    <p class="text-muted">{{ $user->updated_at->format('d M Y, h:i A') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Details Sections -->
            <div class="row">
                <!-- Companies -->
                @if ($user->companies->count() > 0)
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Companies ({{ $user->companies->count() }})</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach ($user->companies as $company)
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3">
                                                <h6 class="fw-semibold">{{ $company->name }}</h6>
                                                <p class="mb-1"><i
                                                        class="ti ti-building text-info me-2"></i>{{ $company->industry }}
                                                </p>
                                                @if ($company->email)
                                                    <p class="mb-1"><i
                                                            class="ti ti-mail text-warning me-2"></i>{{ $company->email }}
                                                    </p>
                                                @endif
                                                @if ($company->phone)
                                                    <p class="mb-0"><i
                                                            class="ti ti-phone text-success me-2"></i>{{ $company->phone }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Locations -->
                @if ($user->locations->count() > 0)
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Locations ({{ $user->locations->count() }})</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach ($user->locations as $location)
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3">
                                                <h6 class="fw-semibold">{{ $location->name }}</h6>
                                                <p class="mb-1"><i
                                                        class="ti ti-mail text-info me-2"></i>{{ $location->email }}</p>
                                                <p class="mb-1"><i
                                                        class="ti ti-map-pin text-warning me-2"></i>{{ $location->address }}
                                                </p>
                                                <p class="mb-0"><i
                                                        class="ti ti-building text-success me-2"></i>{{ $location->city }},
                                                    {{ $location->state }}, {{ $location->country }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Areas -->
                @if ($user->areas->count() > 0)
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Areas ({{ $user->areas->count() }})</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach ($user->areas as $area)
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3">
                                                <h6 class="fw-semibold">{{ $area->name }}</h6>
                                                @if ($area->code)
                                                    <p class="mb-1"><i
                                                            class="ti ti-code text-info me-2"></i>{{ $area->code }}</p>
                                                @endif
                                                @if ($area->description)
                                                    <p class="mb-0"><i
                                                            class="ti ti-note text-warning me-2"></i>{{ $area->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Roles -->
                @if ($user->roles->count() > 0)
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Roles ({{ $user->roles->count() }})</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach ($user->roles as $role)
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3">
                                                <h6 class="fw-semibold">
                                                    <i
                                                        class="ti ti-shield-check text-primary me-2"></i>{{ $role->role_name }}
                                                </h6>
                                                @if ($role->created_at)
                                                    <p class="mb-1 text-muted">
                                                        <i class="ti ti-calendar text-info me-2"></i>
                                                        Created: {{ $role->created_at->format('d M Y') }}
                                                    </p>
                                                @endif
                                                @if ($role->updated_at)
                                                    <p class="mb-0 text-muted">
                                                        <i class="ti ti-clock text-warning me-2"></i>
                                                        Updated: {{ $role->updated_at->format('d M Y') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- End Content -->

    <!-- Edit User Offcanvas -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_edit"
        aria-labelledby="offcanvas_edit_label">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas_edit_label">Edit User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="card">
                <div class="card-body">
                    <form id="edit-user-form" method="POST" action="{{ route('user.update', $user->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="from_show" value="1">
                        <div class="accordion accordion-bordered" id="main_accordion">
                            <!-- Basic Info -->
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#basic">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-user-plus"></i></span>
                                        Basic Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse show" id="basic"
                                    data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">First Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="first_name"
                                                        id="edit-first_name" value="{{ $user->first_name }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Middle Name</label>
                                                    <input type="text" class="form-control" name="middle_name"
                                                        id="edit-middle_name" value="{{ $user->middle_name }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Last Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="last_name"
                                                        id="edit-last_name" value="{{ $user->last_name }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Email <span
                                                            class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" name="email"
                                                        id="edit-email" value="{{ $user->email }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Password</label>
                                                    <input type="password" class="form-control" name="password"
                                                        id="edit-password"
                                                        placeholder="Leave blank to keep current password">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Mobile</label>
                                                    <input type="text" class="form-control" name="mobile"
                                                        id="edit-mobile" value="{{ $user->mobile }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Phone</label>
                                                    <input type="text" class="form-control" name="phone"
                                                        id="edit-phone" value="{{ $user->phone }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Employee ID</label>
                                                    <input type="text" class="form-control" name="employee_id"
                                                        id="edit-employee_id" value="{{ $user->employee_id }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Gender</label>
                                                    <select class="form-select" name="gender" id="edit-gender">
                                                        <option value="">Select Gender</option>
                                                        <option value="1" {{ $user->gender == 1 ? 'selected' : '' }}>
                                                            Male</option>
                                                        <option value="2" {{ $user->gender == 2 ? 'selected' : '' }}>
                                                            Female</option>
                                                        <option value="3" {{ $user->gender == 3 ? 'selected' : '' }}>
                                                            Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Date of Birth</label>
                                                    <input type="date" class="form-control" name="date_of_birth"
                                                        id="edit-date_of_birth" value="{{ $user->date_of_birth }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Date of Joining</label>
                                                    <input type="date" class="form-control" name="date_of_joining"
                                                        id="edit-date_of_joining" value="{{ $user->date_of_joining }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" name="status" id="edit-status">
                                                        <option value="0" {{ $user->status == 0 ? 'selected' : '' }}>
                                                            Delete</option>
                                                        <option value="1" {{ $user->status == 1 ? 'selected' : '' }}>
                                                            Active</option>
                                                        <option value="2" {{ $user->status == 2 ? 'selected' : '' }}>
                                                            Deactivate</option>
                                                        <option value="3" {{ $user->status == 3 ? 'selected' : '' }}>
                                                            Block</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Locations --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#locations">
                                        <span class="avatar avatar-md rounded me-1"><i
                                                class="ti ti-map-pin-cog"></i></span>
                                        Locations
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="locations" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Locations</label>
                                                    <select class="select2 form-control select2-multiple"
                                                        name="location_ids[]" id="edit-location_ids"
                                                        data-toggle="select2" multiple="multiple"
                                                        data-placeholder="Choose locations...">
                                                        @foreach (\App\Models\Location::where('status', 1)->get() as $location)
                                                            <option value="{{ $location->id }}"
                                                                {{ $user->locations->contains($location->id) ? 'selected' : '' }}>
                                                                {{ $location->name }} - {{ $location->city }},
                                                                {{ $location->country }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Companies --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#companies">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-building"></i></span>
                                        Companies
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="companies" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Companies</label>
                                                    <select class="select2 form-control select2-multiple"
                                                        name="company_ids[]" id="edit-company_ids" data-toggle="select2"
                                                        multiple="multiple" data-placeholder="Choose companies...">
                                                        @foreach (\App\Models\Company::where('status', 1)->get() as $company)
                                                            <option value="{{ $company->id }}"
                                                                {{ $user->companies->contains($company->id) ? 'selected' : '' }}>
                                                                {{ $company->name }} - {{ $company->industry }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Areas --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#areas">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-map-pin"></i></span>
                                        Areas
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="areas" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Areas</label>
                                                    <select class="select2 form-control select2-multiple"
                                                        name="area_ids[]" id="edit-area_ids" data-toggle="select2"
                                                        multiple="multiple" data-placeholder="Choose areas...">
                                                        @foreach (\App\Models\Area::where('status', 1)->get() as $area)
                                                            <option value="{{ $area->id }}"
                                                                {{ $user->areas->contains($area->id) ? 'selected' : '' }}>
                                                                {{ $area->name }}{{ $area->code ? ' - ' . $area->code : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Roles --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#edit-roles">
                                        <span class="avatar avatar-md rounded me-1"><i
                                                class="ti ti-shield-check"></i></span>
                                        Roles
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="edit-roles"
                                    data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Roles</label>
                                                    <select class="select2 form-control select2-multiple"
                                                        name="role_ids[]" id="edit-role_ids" data-toggle="select2"
                                                        multiple="multiple" data-placeholder="Choose roles...">
                                                        @foreach (\App\Models\Role::all() as $role)
                                                            <option value="{{ $role->id }}"
                                                                {{ $user->roles->contains($role->id) ? 'selected' : '' }}>
                                                                {{ $role->role_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="edit-form-alert" class="col-12" style="display: none;">
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    User updated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" data-bs-dismiss="offcanvas"
                                class="btn btn-sm btn-light me-2">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Update User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Select2 CSS and JS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>

    <!-- User Show JS -->
    <script src="{{ asset('assets/js/datatable/user-show.js') }}" type="text/javascript"></script>

    <style>
        /* Select2 Custom Styles - Perfect Design Match */
        .select2-container {
            width: 100% !important;
            font-family: inherit !important;
        }

        .select2-container--default .select2-selection--multiple {
            border: 1px solid #d1d3e2 !important;
            border-radius: 0.375rem !important;
            min-height: 38px !important;
            padding: 0.375rem 0.75rem !important;
            background-color: #fff !important;
            box-sizing: border-box !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 6px !important;
            padding: 0 !important;
            margin: 0 !important;
            line-height: normal !important;
            align-items: center !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            position: relative !important;
            background-color: #f8f9fa !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.25rem !important;
            padding: 0.25rem 0.5rem !important;
            margin: 0 !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            color: #495057 !important;
            display: inline-flex !important;
            align-items: center !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            font-weight: 400 !important;
        }

        /* Fix for duplicate cross symbols - hide default Select2 remove button */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: relative !important;
            right: auto !important;
            top: auto !important;
            transform: none !important;
            color: #6c757d !important;
            font-weight: bold !important;
            font-size: 0 !important;
            /* Hide the default text */
            line-height: 0 !important;
            cursor: pointer !important;
            border: none !important;
            background: none !important;
            padding: 0 !important;
            margin: 0 0 0 0.25rem !important;
            width: 16px !important;
            height: 16px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            float: none !important;
            text-decoration: none !important;
            font-family: inherit !important;
            overflow: hidden !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #dc3545 !important;
        }

        /* Show only our custom × symbol */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:before {
            content: "×" !important;
            font-size: 1.125rem !important;
            line-height: 1 !important;
            color: #6c757d !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
        }

        /* Hide any other potential × symbols or text */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:after,
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove span,
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove i {
            display: none !important;
            content: "" !important;
        }

        .select2-container--default .select2-search--inline {
            margin: 0 !important;
            padding: 0 !important;
            flex: 1 !important;
            min-width: 120px !important;
        }

        .select2-container--default .select2-search--inline .select2-search__field {
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            width: 100% !important;
            background: transparent !important;
            color: #495057 !important;
        }

        .select2-container--default .select2-dropdown {
            border: 1px solid #d1d3e2 !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
            background-color: #fff !important;
            z-index: 9999 !important;
            margin-top: 2px !important;
        }

        .select2-container--default .select2-results__option {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
            line-height: 1.5 !important;
            color: #495057 !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #0d6efd !important;
            color: white !important;
        }

        .select2-container--default .select2-results__group {
            font-weight: 600 !important;
            color: #6c757d !important;
            padding: 0.5rem 0.75rem 0.25rem !important;
            font-size: 0.875rem !important;
            background-color: #f8f9fa !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e9ecef !important;
            color: #495057 !important;
        }

        /* Focus state */
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }

        /* Placeholder styling */
        .select2-container--default .select2-selection--multiple .select2-selection__placeholder {
            color: #6c757d !important;
            font-size: 0.875rem !important;
        }
    </style>

    <!-- Store user data globally for JavaScript access -->
    <script>
        window.currentUserData = @json($user);
        console.log('User data stored globally:', window.currentUserData);
    </script>
@endpush
