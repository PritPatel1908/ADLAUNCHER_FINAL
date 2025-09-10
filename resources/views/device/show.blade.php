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
                    <h4 class="mb-1">Device Details</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('device.index') }}">Devices</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Device Details</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    @if (\App\Helpers\PermissionHelper::canExport('device'))
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
                        <a href="{{ route('device.index') }}"><i class="ti ti-arrow-narrow-left me-1"></i>Back to
                            Devices</a>
                    </div>

                    <!-- Device Header Card -->
                    <div class="card">
                        <div class="card-body pb-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-xxl avatar-rounded me-3 flex-shrink-0 bg-primary">
                                        <i class="ti ti-device-laptop"></i>
                                        <span class="status {{ $device->status == 1 ? 'online' : 'offline' }}"></span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">{{ $device->name }}</h5>
                                        <p class="mb-2">{{ $device->unique_id }}</p>
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
                                                        'text' => 'Inactive',
                                                        'class' => 'badge-soft-warning',
                                                        'icon' => 'ti-player-pause',
                                                    ],
                                                    3 => [
                                                        'text' => 'Block',
                                                        'class' => 'badge-soft-danger',
                                                        'icon' => 'ti-lock',
                                                    ],
                                                ];
                                                $s = $statusMap[$device->status] ?? [
                                                    'text' => 'Unknown',
                                                    'class' => 'badge-soft-secondary',
                                                    'icon' => 'ti-circle',
                                                ];
                                            @endphp
                                            <span id="header-status-badge" class="badge {{ $s['class'] }} border-0 me-2">
                                                <i class="ti {{ $s['icon'] }} me-1"></i>
                                                {{ $s['text'] }}
                                            </span>
                                            @if ($device->ip)
                                                <p class="d-inline-flex align-items-center mb-0 me-3">
                                                    <i class="ti ti-network text-info me-1"></i> {{ $device->ip }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    @if (\App\Helpers\PermissionHelper::canEdit('device'))
                                        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas"
                                            data-bs-target="#offcanvas_edit">
                                            <i class="ti ti-edit me-1"></i>Edit Device
                                        </a>
                                    @endif
                                    @if (\App\Helpers\PermissionHelper::canDelete('device'))
                                        <form action="{{ route('device.destroy', $device->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this device?')">
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

            <!-- Device Information Cards -->
            <div class="row">
                <!-- Device Overview -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Device Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Device Name</h6>
                                        <p>{{ $device->name }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Unique ID</h6>
                                        <p>{{ $device->unique_id }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">IP Address</h6>
                                        <p>{{ $device->ip ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Company</h6>
                                        <p>{{ $device->company ? $device->company->name : 'N/A' }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Location</h6>
                                        <p>{{ $device->location ? $device->location->name : 'N/A' }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Status</h6>
                                        @php
                                            $s2 = $statusMap[$device->status] ?? [
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

                <!-- Device Statistics -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Device Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Area</h6>
                                        <p class="h4 text-primary">{{ $device->area ? $device->area->name : 'N/A' }}</p>
                                    </div>
                                    {{-- <div class="mb-4">
                                        <h6 class="fw-semibold">Layouts Count</h6>
                                        <div class="d-flex gap-2">
                                            <span class="badge badge-soft-primary fs-6">Total: {{ $device->layouts_count }}</span>
                                            <span class="badge badge-soft-success fs-6">Active: {{ $device->active_layouts_count }}</span>
                                        </div>
                                    </div> --}}
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Created By</h6>
                                        <p class="h4 text-success">
                                            {{ $device->createdByUser ? $device->createdByUser->full_name : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Updated By</h6>
                                        <p class="h4 text-warning">
                                            {{ $device->updatedByUser ? $device->updatedByUser->full_name : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6 class="fw-semibold">Created</h6>
                                <p class="text-muted">{{ $device->created_at->format('d M Y, h:i A') }}</p>
                                @if ($device->updated_at)
                                    <h6 class="fw-semibold">Last Updated</h6>
                                    <p class="text-muted">{{ $device->updated_at->format('d M Y, h:i A') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device Layouts Section -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-0">Device Layouts</h5>
                                <div class="d-flex gap-2 mt-1">
                                    <span class="badge badge-soft-primary">Total: {{ $device->layouts_count }}</span>
                                    <span class="badge badge-soft-success">Active:
                                        {{ $device->active_layouts_count }}</span>
                                    <span class="badge badge-soft-warning">Inactive:
                                        {{ $device->getLayoutsByStatus(2) }}</span>
                                    <span class="badge badge-soft-danger">Blocked:
                                        {{ $device->getLayoutsByStatus(3) }}</span>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="offcanvas"
                                data-bs-target="#offcanvas_layout_management">
                                <i class="ti ti-plus me-1"></i>Add Layout
                            </button>
                        </div>
                        <div class="card-body">
                            @if ($device->deviceLayouts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Layout Name</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($device->deviceLayouts as $layout)
                                                <tr>
                                                    <td>{{ $layout->layout_name }}</td>
                                                    <td>
                                                        <span class="badge badge-soft-info">
                                                            {{ $layout->layout_type_name }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusMap = [
                                                                0 => [
                                                                    'class' => 'badge-soft-secondary',
                                                                    'icon' => 'ti-trash',
                                                                    'text' => 'Delete',
                                                                ],
                                                                1 => [
                                                                    'class' => 'badge-soft-success',
                                                                    'icon' => 'ti-check',
                                                                    'text' => 'Active',
                                                                ],
                                                                2 => [
                                                                    'class' => 'badge-soft-warning',
                                                                    'icon' => 'ti-player-pause',
                                                                    'text' => 'Inactive',
                                                                ],
                                                                3 => [
                                                                    'class' => 'badge-soft-danger',
                                                                    'icon' => 'ti-lock',
                                                                    'text' => 'Block',
                                                                ],
                                                            ];
                                                            $s = $statusMap[$layout->status] ?? [
                                                                'class' => 'badge-soft-secondary',
                                                                'icon' => 'ti-circle',
                                                                'text' => 'Unknown',
                                                            ];
                                                        @endphp
                                                        <span class="badge {{ $s['class'] }}">
                                                            <i
                                                                class="ti {{ $s['icon'] }} me-1"></i>{{ $s['text'] }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $layout->created_at->format('d M Y, h:i A') }}</td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-primary edit-layout-btn"
                                                                data-layout-id="{{ $layout->id }}"
                                                                data-layout-name="{{ $layout->layout_name }}"
                                                                data-layout-type="{{ $layout->layout_type }}"
                                                                data-layout-status="{{ $layout->status }}">
                                                                <i class="ti ti-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger delete-layout-btn"
                                                                data-layout-id="{{ $layout->id }}">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="ti ti-layout-grid text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="text-muted mt-2">No layouts found</h6>
                                    <p class="text-muted">This device doesn't have any layouts configured yet.</p>
                                    <button class="btn btn-primary" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvas_layout_management">
                                        <i class="ti ti-plus me-1"></i>Add First Layout
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device Screens Section -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-0">Device Screens</h5>
                                <div class="d-flex gap-2 mt-1">
                                    <span class="badge badge-soft-primary">Total: {{ $device->screens_count }}</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-info btn-sm" data-bs-toggle="offcanvas"
                                    data-bs-target="#offcanvas_screen_preview" id="preview-screens-btn">
                                    <i class="ti ti-eye me-1"></i>Preview Screens
                                </button>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="offcanvas"
                                    data-bs-target="#offcanvas_screen_management">
                                    <i class="ti ti-plus me-1"></i>Add Screen
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($device->deviceScreens->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Screen No</th>
                                                <th>Height</th>
                                                <th>Width</th>
                                                <th>Layout</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($device->deviceScreens as $screen)
                                                <tr>
                                                    <td>{{ $screen->screen_no }}</td>
                                                    <td>{{ $screen->screen_height }}</td>
                                                    <td>{{ $screen->screen_width }}</td>
                                                    <td>{{ optional($screen->layout)->layout_name }}</td>
                                                    <td>{{ $screen->created_at->format('d M Y, h:i A') }}</td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-sm btn-outline-primary edit-screen-btn"
                                                                data-screen-id="{{ $screen->id }}"
                                                                data-screen-no="{{ $screen->screen_no }}"
                                                                data-screen-height="{{ $screen->screen_height }}"
                                                                data-screen-width="{{ $screen->screen_width }}"
                                                                data-layout-id="{{ $screen->layout_id }}">
                                                                <i class="ti ti-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger delete-screen-btn"
                                                                data-screen-id="{{ $screen->id }}">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="ti ti-layout-grid text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="text-muted mt-2">No screens found</h6>
                                    <p class="text-muted">This device doesn't have any screens configured yet.</p>
                                    <button class="btn btn-primary" data-bs-toggle="offcanvas"
                                        data-bs-target="#offcanvas_screen_management">
                                        <i class="ti ti-plus me-1"></i>Add First Screen
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device Details Sections -->
            <div class="row">
                <!-- Company Details -->
                @if ($device->company)
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Company Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h6 class="fw-semibold">{{ $device->company->name }}</h6>
                                            <p class="mb-1"><i
                                                    class="ti ti-building text-info me-2"></i>{{ $device->company->industry }}
                                            </p>
                                            @if ($device->company->email)
                                                <p class="mb-1"><i
                                                        class="ti ti-mail text-warning me-2"></i>{{ $device->company->email }}
                                                </p>
                                            @endif
                                            @if ($device->company->phone)
                                                <p class="mb-0"><i
                                                        class="ti ti-phone text-success me-2"></i>{{ $device->company->phone }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Location Details -->
                @if ($device->location)
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Location Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h6 class="fw-semibold">{{ $device->location->name }}</h6>
                                            <p class="mb-1"><i
                                                    class="ti ti-mail text-info me-2"></i>{{ $device->location->email }}
                                            </p>
                                            <p class="mb-1"><i
                                                    class="ti ti-map-pin text-warning me-2"></i>{{ $device->location->address }}
                                            </p>
                                            <p class="mb-0"><i
                                                    class="ti ti-building text-success me-2"></i>{{ $device->location->city }},
                                                {{ $device->location->state }}, {{ $device->location->country }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Area Details -->
                @if ($device->area)
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Area Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h6 class="fw-semibold">{{ $device->area->name }}</h6>
                                            <p class="mb-1"><i
                                                    class="ti ti-map-pin text-info me-2"></i>{{ $device->area->city }},
                                                {{ $device->area->state }}</p>
                                            @if ($device->area->description)
                                                <p class="mb-0"><i
                                                        class="ti ti-note text-warning me-2"></i>{{ $device->area->description }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- End Content -->

    <!-- Edit Device Offcanvas -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_edit"
        aria-labelledby="offcanvas_edit_label">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas_edit_label">Edit Device</h5>
            <button type="button"
                class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle"
                data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body">
            <div class="card">
                <div class="card-body">
                    <form id="edit-device-form" method="POST" action="{{ route('device.update', $device->id) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="from_show" value="1">
                        <div class="accordion accordion-bordered" id="main_accordion">
                            <!-- Basic Info -->
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#basic">
                                        <span class="avatar avatar-md rounded me-1"><i
                                                class="ti ti-device-laptop"></i></span>
                                        Device Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse show" id="basic"
                                    data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name"
                                                        id="edit-name" value="{{ $device->name }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Unique ID <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="unique_id"
                                                        id="edit-unique_id" value="{{ $device->unique_id }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">IP</label>
                                                    <input type="text" class="form-control" name="ip"
                                                        id="edit-ip" value="{{ $device->ip }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Company</label>
                                                    <select class="form-control select2" name="company_id"
                                                        id="edit-company_id" data-toggle="select2">
                                                        <option value="">Select company...</option>
                                                        @foreach (\App\Models\Company::where('status', 1)->get() as $company)
                                                            <option value="{{ $company->id }}"
                                                                {{ $device->company_id == $company->id ? 'selected' : '' }}>
                                                                {{ $company->name }} - {{ $company->industry }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Location</label>
                                                    <select class="form-control select2" name="location_id"
                                                        id="edit-location_id" data-toggle="select2">
                                                        <option value="">Select location...</option>
                                                        @foreach (\App\Models\Location::where('status', 1)->get() as $location)
                                                            <option value="{{ $location->id }}"
                                                                {{ $device->location_id == $location->id ? 'selected' : '' }}>
                                                                {{ $location->name }} - {{ $location->city }},
                                                                {{ $location->country }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Area</label>
                                                    <select class="form-control select2" name="area_id" id="edit-area_id"
                                                        data-toggle="select2">
                                                        <option value="">Select area...</option>
                                                        @foreach (\App\Models\Area::where('status', 1)->get() as $area)
                                                            <option value="{{ $area->id }}"
                                                                {{ $device->area_id == $area->id ? 'selected' : '' }}>
                                                                {{ $area->name }} - {{ $area->city }},
                                                                {{ $area->state }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" name="status" id="edit-status">
                                                        <option value="0"
                                                            {{ $device->status == 0 ? 'selected' : '' }}>Delete</option>
                                                        <option value="1"
                                                            {{ $device->status == 1 ? 'selected' : '' }}>Active</option>
                                                        <option value="2"
                                                            {{ $device->status == 2 ? 'selected' : '' }}>Inactive</option>
                                                        <option value="3"
                                                            {{ $device->status == 3 ? 'selected' : '' }}>Block</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="edit-form-alert" class="col-12" style="display: none;">
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Device updated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" data-bs-dismiss="offcanvas"
                                class="btn btn-sm btn-light me-2">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Update Device</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Layout Management Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_layout_management">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Device Layout Management</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <!-- Add/Edit Layout Form -->
            <div class="mb-4">
                <h6 id="layout-form-title">Add New Layout</h6>
                <form id="layout-form">
                    @csrf
                    <input type="hidden" id="layout-id" name="layout_id">
                    <input type="hidden" id="layout-device-id" name="device_id" value="{{ $device->id }}">

                    <div class="mb-3">
                        <label for="layout-name" class="form-label">Layout Name</label>
                        <input type="text" class="form-control" id="layout-name" name="layout_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="layout-type" class="form-label">Layout Type</label>
                        <select class="form-select" id="layout-type" name="layout_type" required>
                            <option value="">Select Layout Type</option>
                            <option value="0">Full Screen</option>
                            <option value="1">Split Screen</option>
                            <option value="2">Three Grid Screen</option>
                            <option value="3">Four Grid Screen</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="layout-status" class="form-label">Status</label>
                        <select class="form-select" id="layout-status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="0">Delete</option>
                            <option value="1">Active</option>
                            <option value="2">Inactive</option>
                            <option value="3">Block</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="layout-submit-btn">Add Layout</button>
                        <button type="button" class="btn btn-secondary" id="layout-cancel-btn"
                            style="display: none;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Device Screen Management Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_screen_management">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Device Screen Management</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <!-- Add/Edit Screen Form -->
            <div class="mb-4">
                <h6 id="screen-form-title">Add New Screen</h6>
                <div id="screen-form-alert" class="mt-2" style="display: none;"></div>
                <form id="screen-form">
                    @csrf
                    <input type="hidden" id="screen-id" name="screen_id">
                    <input type="hidden" id="screen-device-id" name="device_id" value="{{ $device->id }}">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="screen-no" class="form-label">Screen No</label>
                                <input type="number" min="1" class="form-control" id="screen-no"
                                    name="screen_no" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="screen-height" class="form-label">Height</label>
                                <input type="number" min="1" class="form-control" id="screen-height"
                                    name="screen_height" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="screen-width" class="form-label">Width</label>
                                <input type="number" min="1" class="form-control" id="screen-width"
                                    name="screen_width" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="screen-layout-id" class="form-label">Layout</label>
                        <select class="form-select" id="screen-layout-id" name="layout_id" required>
                            <option value="">Select Layout</option>
                            @foreach ($device->deviceLayouts->where('status', 1) as $layout)
                                <option value="{{ $layout->id }}" data-layout-type="{{ $layout->layout_type }}"
                                    data-layout-type-name="{{ $layout->layout_type_name }}">
                                    {{ $layout->layout_name }} ({{ $layout->layout_type_name }})
                                </option>
                            @endforeach
                        </select>
                        <div id="layout-info" class="mt-2" style="display: none;">
                            <small class="text-muted">
                                <span id="layout-type-info"></span> -
                                <span id="layout-limit-info"></span>
                            </small>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="screen-submit-btn">Add Screen</button>
                        <button type="button" class="btn btn-secondary" id="screen-cancel-btn"
                            style="display: none;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Device Screen Preview Offcanvas -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_screen_preview">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Device Screen Preview</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Screen Layout Preview</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" id="refresh-preview-btn">
                            <i class="ti ti-refresh me-1"></i>Refresh
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="ti ti-settings me-1"></i>Options
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" id="show-dimensions-btn">
                                        <i class="ti ti-ruler me-1"></i>Show Dimensions
                                    </a></li>
                                <li><a class="dropdown-item" href="#" id="show-screen-numbers-btn">
                                        <i class="ti ti-hash me-1"></i>Show Screen Numbers
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div id="screen-preview-container" class="screen-preview-container">
                <div class="text-center py-5" id="preview-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading screen preview...</p>
                </div>
                <div id="preview-content" style="display: none;">
                    <!-- Screen previews will be rendered here -->
                </div>
                <div id="preview-empty" class="text-center py-5" style="display: none;">
                    <i class="ti ti-layout-grid text-muted" style="font-size: 3rem;"></i>
                    <h6 class="text-muted mt-2">No screens to preview</h6>
                    <p class="text-muted">This device doesn't have any screens configured yet.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Select2 CSS and JS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>

    <!-- Device Show JS -->
    <script src="{{ asset('assets/js/datatable/device-show.js') }}" type="text/javascript"></script>

    <style>
        /* Select2 Custom Styles */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #d1d3e2 !important;
            border-radius: 0.375rem !important;
            padding: 0.375rem 0.75rem !important;
            background-color: #fff !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            padding-left: 0 !important;
            padding-right: 20px !important;
            color: #495057 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #6c757d !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }

        .select2-dropdown {
            border: 1px solid #d1d3e2 !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        .select2-container--default .select2-results__option {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #0d6efd !important;
            color: white !important;
        }

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e9ecef !important;
            color: #495057 !important;
        }

        /* Screen Preview Styles */
        .screen-preview-container {
            min-height: 400px;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            position: relative;
        }

        .screen-preview-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            align-items: flex-start;
        }

        .screen-preview-item {
            background: #fff;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .screen-preview-item:hover {
            border-color: #0d6efd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .screen-preview-item .screen-content {
            text-align: center;
            padding: 10px;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .screen-preview-item .screen-number {
            font-weight: bold;
            color: #0d6efd;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .screen-preview-item .screen-dimensions {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .screen-preview-item .screen-layout {
            font-size: 11px;
            color: #28a745;
            background: #d4edda;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .screen-preview-item .screen-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            display: none;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 12px;
            text-align: center;
        }

        .screen-preview-item:hover .screen-overlay {
            display: flex;
        }

        .preview-options {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }

        .preview-legend {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            margin-top: 15px;
            font-size: 12px;
        }

        .preview-legend .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .preview-legend .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            margin-right: 8px;
        }

        .layout-group {
            margin-bottom: 20px;
        }

        .layout-group-title {
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
        }
    </style>

    <script>
        $(document).ready(function() {
            console.log('jQuery loaded:', typeof $ !== 'undefined');
            console.log('Select2 loaded:', typeof $.fn.select2 !== 'undefined');
            console.log('Initializing Select2...');

            // Initialize Select2 for single select elements
            function initializeSelect2() {
                $('select[data-toggle="select2"]').each(function() {
                    if (!$(this).hasClass('select2-initialized')) {
                        console.log('Initializing Select2 for:', this);
                        $(this).select2({
                            placeholder: 'Select...',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $(this).closest('.offcanvas-body, .modal-body, body')
                        });
                        $(this).addClass('select2-initialized');
                    }
                });
            }

            // Initialize Select2 on page load
            initializeSelect2();

            // Re-initialize Select2 after dynamic content is added
            $(document).on('shown.bs.offcanvas', function() {
                console.log('Offcanvas shown, re-initializing Select2...');
                setTimeout(function() {
                    initializeSelect2();
                }, 100);
            });

            // Force re-initialization after a short delay
            setTimeout(function() {
                console.log('Force re-initializing Select2...');
                initializeSelect2();
            }, 1000);

            // Screen Preview Functionality
            let showDimensions = false;
            let showScreenNumbers = true;

            // Preview screens button click
            $('#preview-screens-btn').on('click', function() {
                loadScreenPreview();
            });

            // Refresh preview button
            $('#refresh-preview-btn').on('click', function() {
                loadScreenPreview();
            });

            // Show dimensions toggle
            $('#show-dimensions-btn').on('click', function(e) {
                e.preventDefault();
                showDimensions = !showDimensions;
                $(this).find('i').toggleClass('ti-ruler ti-ruler-off');
                renderScreenPreview();
            });

            // Show screen numbers toggle
            $('#show-screen-numbers-btn').on('click', function(e) {
                e.preventDefault();
                showScreenNumbers = !showScreenNumbers;
                $(this).find('i').toggleClass('ti-hash ti-hash-off');
                renderScreenPreview();
            });

            function loadScreenPreview() {
                const deviceId = {{ $device->id }};

                // Show loading
                $('#preview-loading').show();
                $('#preview-content').hide();
                $('#preview-empty').hide();

                $.ajax({
                    url: `/device/${deviceId}/screens`,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#preview-loading').hide();

                        if (response.success && response.screens && response.screens.length > 0) {
                            window.screenPreviewData = response.screens;
                            renderScreenPreview();
                            $('#preview-content').show();
                        } else {
                            $('#preview-empty').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#preview-loading').hide();
                        $('#preview-empty').show();
                        console.error('Error loading screen preview:', error);
                    }
                });
            }

            function renderScreenPreview() {
                if (!window.screenPreviewData) return;

                const screens = window.screenPreviewData;
                const container = $('#preview-content');

                // Group screens by layout
                const layoutGroups = {};
                screens.forEach(screen => {
                    const layoutName = screen.layout ? screen.layout.layout_name : 'No Layout';
                    if (!layoutGroups[layoutName]) {
                        layoutGroups[layoutName] = [];
                    }
                    layoutGroups[layoutName].push(screen);
                });

                let html = '';

                // Render each layout group
                Object.keys(layoutGroups).forEach(layoutName => {
                    const layoutScreens = layoutGroups[layoutName];
                    html += `<div class="layout-group">`;
                    html +=
                        `<div class="layout-group-title">${layoutName} (${layoutScreens.length} screens)</div>`;
                    html += `<div class="screen-preview-grid">`;

                    layoutScreens.forEach(screen => {
                        const aspectRatio = screen.screen_width / screen.screen_height;
                        const baseSize = 120; // Base size in pixels
                        const width = Math.max(80, Math.min(200, baseSize * aspectRatio));
                        const height = Math.max(60, Math.min(150, baseSize / aspectRatio));

                        html += `
                            <div class="screen-preview-item" style="width: ${width}px; height: ${height}px;"
                                 data-screen-id="${screen.id}"
                                 data-screen-no="${screen.screen_no}"
                                 data-width="${screen.screen_width}"
                                 data-height="${screen.screen_height}">
                                <div class="screen-content">
                                    ${showScreenNumbers ? `<div class="screen-number">Screen ${screen.screen_no}</div>` : ''}
                                    ${showDimensions ? `<div class="screen-dimensions">${screen.screen_width} × ${screen.screen_height}</div>` : ''}
                                    <div class="screen-layout">${screen.layout ? screen.layout.layout_name : 'No Layout'}</div>
                                </div>
                                <div class="screen-overlay">
                                    <div>
                                        <div><strong>Screen ${screen.screen_no}</strong></div>
                                        <div>${screen.screen_width} × ${screen.screen_height}</div>
                                        <div>${screen.layout ? screen.layout.layout_name : 'No Layout'}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += `</div></div>`;
                });

                // Add legend
                html += `
                    <div class="preview-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: #0d6efd;"></div>
                            <span>Screen Number</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #6c757d;"></div>
                            <span>Dimensions (Width × Height)</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #d4edda;"></div>
                            <span>Layout Name</span>
                        </div>
                    </div>
                `;

                container.html(html);

                // Add click handlers for screen items
                $('.screen-preview-item').on('click', function() {
                    const screenId = $(this).data('screen-id');
                    const screenNo = $(this).data('screen-no');
                    const width = $(this).data('width');
                    const height = $(this).data('height');

                    // You can add more functionality here, like showing detailed info
                    alert(`Screen ${screenNo}\nDimensions: ${width} × ${height}\nID: ${screenId}`);
                });
            }

            // Load preview when offcanvas is shown
            $('#offcanvas_screen_preview').on('shown.bs.offcanvas', function() {
                if (!window.screenPreviewData) {
                    loadScreenPreview();
                }
            });
        });
    </script>
@endpush
