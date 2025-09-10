@extends('layout.main')

@section('meta')
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('css')
@endpush

@section('content')
    <div class="content pb-0">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Schedules</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Schedules</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    @if (\App\Helpers\PermissionHelper::canExport('schedule'))
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-outline-primary px-2 shadow"
                                data-bs-toggle="dropdown"><i class="ti ti-package-export me-2"></i>Export</a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <ul>
                                    <li><a href="javascript:void(0);" class="dropdown-item"><i
                                                class="ti ti-file-type-pdf me-1"></i>Export as PDF</a></li>
                                    <li><a href="javascript:void(0);" class="dropdown-item"><i
                                                class="ti ti-file-type-xls me-1"></i>Export as Excel</a></li>
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

            <div class="card border-0 rounded-0">
                <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="input-icon input-icon-start position-relative">
                        <span class="input-icon-addon text-dark"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search">
                    </div>
                    @if (\App\Helpers\PermissionHelper::canCreate('schedule'))
                        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvas_add">
                            <i class="ti ti-square-rounded-plus-filled me-1"></i>Add Schedule
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle btn btn-outline-light px-2 shadow"
                                    data-bs-toggle="dropdown"><i class="ti ti-sort-ascending-2 me-2"></i>Sort By</a>
                                <div class="dropdown-menu">
                                    <ul>
                                        <li><a href="javascript:void(0);" class="dropdown-item sort-option"
                                                data-sort="newest">Newest</a></li>
                                        <li><a href="javascript:void(0);" class="dropdown-item sort-option"
                                                data-sort="oldest">Oldest</a></li>
                                        <li><a href="javascript:void(0);" class="dropdown-item sort-option"
                                                data-sort="name_asc">Name A-Z</a></li>
                                        <li><a href="javascript:void(0);" class="dropdown-item sort-option"
                                                data-sort="name_desc">Name Z-A</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div id="reportrange" class="reportrange-picker d-flex align-items-center shadow">
                                <i class="ti ti-calendar-due text-dark fs-14 me-1"></i><span
                                    class="reportrange-picker-field">9 Jun 25 - 9 Jun 25</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="btn btn-outline-light shadow px-2"
                                    data-bs-toggle="dropdown" data-bs-auto-close="outside"><i
                                        class="ti ti-filter me-2"></i>Filter<i class="ti ti-chevron-down ms-2"></i></a>
                                <div class="filter-dropdown-menu dropdown-menu dropdown-menu-lg p-0">
                                    <div
                                        class="filter-header d-flex align-items-center justify-content-between border-bottom">
                                        <h6 class="mb-0"><i class="ti ti-filter me-1"></i>Filter</h6>
                                        <button type="button" class="btn-close close-filter-btn"
                                            data-bs-dismiss="dropdown-menu" aria-label="Close"></button>
                                    </div>
                                    <div class="filter-set-view p-3">
                                        <div class="accordion" id="accordionExample">
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseName" aria-expanded="true"
                                                        aria-controls="collapseName">Schedule Name</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse show"
                                                    id="collapseName" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12"><i
                                                                        class="ti ti-search"></i></span>
                                                                <input type="text"
                                                                    class="form-control form-control-md schedule-filter"
                                                                    placeholder="Search" data-column="schedule_name">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseDevice" aria-expanded="false"
                                                        aria-controls="collapseDevice">Device</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseDevice" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12"><i
                                                                        class="ti ti-search"></i></span>
                                                                <input type="text"
                                                                    class="form-control form-control-md schedule-filter"
                                                                    placeholder="Device ID" data-column="device">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="btn bg-soft-indigo px-2 border-0"
                                    data-bs-toggle="dropdown" data-bs-auto-close="outside"><i
                                        class="ti ti-columns-3 me-2"></i>Manage Columns</a>
                                <div class="dropdown-menu dropdown-menu-md dropdown-md p-3">
                                    <ul id="column-visibility-list">
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Name</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked
                                                        data-column="schedule_name">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Device</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="device">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Layout</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="layout">
                                                </label>
                                            </div>
                                        </li>

                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Created At</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="created_at">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Action</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="action">
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="d-flex align-items-center shadow p-1 rounded border view-icons bg-white">
                                <a href="javascript:void(0);" class="btn btn-sm p-1 border-0 fs-14 active"><i
                                        class="ti ti-list-tree"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="data-loading" style="display: none;">
                        <div class="d-flex justify-content-center align-items-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="ms-2">Loading data...</span>
                        </div>
                    </div>
                    <div class="table-responsive custom-table">
                        <table class="table table-nowrap" id="scheduleslist">
                            <thead class="table-light">
                                <tr>
                                    <th>schedule_name</th>
                                    <th>device</th>
                                    <th>layout</th>

                                    <th>created_at</th>
                                    <th class="text-end no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div id="error-container" class="alert alert-danger mt-3" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <strong>Error!</strong> <span id="error-message">Unable to load data.</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="retry-load">
                                <i class="ti ti-refresh me-1"></i>Retry
                            </button>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="datatable-length"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="datatable-paginate"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Start Add Schedule -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_add">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Add Schedule</h5>
            <button type="button"
                class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle"
                data-bs-dismiss="offcanvas" aria-label="Close"><i class="ti ti-x"></i></button>
        </div>
        <div class="offcanvas-body">
            <div class="card">
                <div class="card-body">
                    <form id="create-schedule-form" method="POST" action="{{ route('schedule.store') }}">
                        @csrf
                        <div class="accordion accordion-bordered" id="main_accordion">
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#basic">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-calendar"></i></span>
                                        Schedule Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse show" id="basic"
                                    data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="schedule_name"
                                                        required>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Device <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-control select2" name="device_id"
                                                        data-toggle="select2" required>
                                                        <option value="">Select device...</option>
                                                        @foreach (\App\Models\Device::where('status', 1)->get() as $device)
                                                            <option value="{{ $device->id }}">{{ $device->name }} -
                                                                {{ $device->unique_id }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Layout</label>
                                                    <select class="form-control select2" name="layout_id"
                                                        data-toggle="select2">
                                                        <option value="">Select layout...</option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#media">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-photo"></i></span>
                                        Schedule Media
                                        <span class="badge badge-soft-info ms-2">Multiple Media Support</span>
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="media" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div id="media-container">
                                            <div class="media-item border rounded p-3 mb-3">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">Media Title</label>
                                                            <input type="text" class="form-control"
                                                                name="media_title[]" placeholder="Enter media title">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Media Type</label>
                                                            <select class="form-control select2" name="media_type[]"
                                                                data-toggle="select2">
                                                                <option value="">Select type...</option>
                                                                <option value="image">Image</option>
                                                                <option value="video">Video</option>
                                                                <option value="audio">Audio</option>
                                                                <option value="mp4">MP4</option>
                                                                <option value="png">PNG</option>
                                                                <option value="jpg">JPG</option>
                                                                <option value="pdf">PDF</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Screen</label>
                                                            <select class="form-control select2" name="media_screen_id[]"
                                                                data-toggle="select2">
                                                                <option value="">Select screen...</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="mb-3">
                                                            <label class="form-label">&nbsp;</label>
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm w-100 remove-media">
                                                                <i class="ti ti-trash"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Duration (seconds)</label>
                                                            <input type="number"
                                                                class="form-control media-duration-input"
                                                                name="media_duration_seconds[]" min="1"
                                                                max="86400" placeholder="e.g. 15">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Start At</label>
                                                            <input type="datetime-local" class="form-control"
                                                                name="media_start_date_time[]">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">End At</label>
                                                            <input type="datetime-local" class="form-control"
                                                                name="media_end_date_time[]">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 d-flex align-items-center">
                                                        <div class="form-check mt-4">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="media_play_forever[]" value="1">
                                                            <label class="form-check-label">Play Forever</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <label class="form-label">Media File</label>
                                                            <input type="file" class="form-control media-file-input"
                                                                name="media_file[]"
                                                                accept="image/*,video/*,audio/*,.mp4,.png,.jpg,.pdf">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-primary btn-sm" id="add-media">
                                                <i class="ti ti-plus me-1"></i>Add Another Media
                                            </button>
                                            <p class="text-muted mt-2 mb-0">
                                                <small><i class="ti ti-info-circle me-1"></i>You can add multiple media
                                                    files and assign them to different screens</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="create-form-alert" class="col-12" style="display: none;">
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Schedule created successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>
                            <div id="upload-progress-container" class="col-12" style="display: none;">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Uploading Files...</h6>
                                        <div class="progress mb-2">
                                            <div id="upload-progress-bar"
                                                class="progress-bar progress-bar-striped progress-bar-animated"
                                                role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                                                aria-valuemax="100">
                                                <span id="upload-progress-text">0%</span>
                                            </div>
                                        </div>
                                        <small id="upload-status-text" class="text-muted">Preparing upload...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" data-bs-dismiss="offcanvas"
                                class="btn btn-sm btn-light me-2">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Create Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Start Edit Schedule -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_edit"
        aria-labelledby="offcanvas_edit_label">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas_edit_label">Edit Schedule</h5>
            <button type="button"
                class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle"
                data-bs-dismiss="offcanvas" aria-label="Close"><i class="ti ti-x"></i></button>
        </div>
        <div class="offcanvas-body">
            <div class="card">
                <div class="card-body">
                    <form id="edit-schedule-form" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="accordion accordion-bordered" id="edit_main_accordion">
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#edit_basic">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-calendar"></i></span>
                                        Schedule Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse show" id="edit_basic"
                                    data-bs-parent="#edit_main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="schedule_name"
                                                        id="edit-schedule_name" required>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Device</label>
                                                    <select class="form-control select2" name="device_id"
                                                        id="edit-device_id" data-toggle="select2">
                                                        <option value="">Select device...</option>
                                                        @foreach (\App\Models\Device::where('status', 1)->get() as $device)
                                                            <option value="{{ $device->id }}">{{ $device->name }} -
                                                                {{ $device->unique_id }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Layout</label>
                                                    <select class="form-control select2" name="layout_id"
                                                        id="edit-layout_id" data-toggle="select2">
                                                        <option value="">Select layout...</option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#edit_media">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-photo"></i></span>
                                        Schedule Media
                                        {{-- <span class="badge badge-soft-info ms-2">Multiple Media Support</span> --}}
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="edit_media"
                                    data-bs-parent="#edit_main_accordion">
                                    <div class="accordion-body border-top">
                                        <div id="edit-media-container">
                                            <div class="media-item border rounded p-3 mb-3">
                                                <input type="hidden" name="edit_media_id[]" value="">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">Media Title</label>
                                                            <input type="text" class="form-control"
                                                                name="edit_media_title[]" placeholder="Enter media title">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Media Type</label>
                                                            <select class="form-control select2" name="edit_media_type[]"
                                                                data-toggle="select2">
                                                                <option value="">Select type...</option>
                                                                <option value="image">Image</option>
                                                                <option value="video">Video</option>
                                                                <option value="audio">Audio</option>
                                                                <option value="mp4">MP4</option>
                                                                <option value="png">PNG</option>
                                                                <option value="jpg">JPG</option>
                                                                <option value="pdf">PDF</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Screen</label>
                                                            <select class="form-control select2"
                                                                name="edit_media_screen_id[]" data-toggle="select2">
                                                                <option value="">Select screen...</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="mb-3">
                                                            <label class="form-label">&nbsp;</label>
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm w-100 remove-media">
                                                                <i class="ti ti-trash"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Duration (seconds)</label>
                                                            <input type="number" class="form-control"
                                                                name="edit_media_duration_seconds[]" min="1"
                                                                max="86400" placeholder="e.g. 15">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">Start At</label>
                                                            <input type="datetime-local" class="form-control"
                                                                name="edit_media_start_date_time[]">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-3">
                                                            <label class="form-label">End At</label>
                                                            <input type="datetime-local" class="form-control"
                                                                name="edit_media_end_date_time[]">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 d-flex align-items-center">
                                                        <div class="form-check mt-4">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="edit_media_play_forever[]" value="1">
                                                            <label class="form-check-label">Play Forever</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="mb-3">
                                                            <label class="form-label">Media File</label>
                                                            <input type="file" class="form-control"
                                                                name="edit_media_file[]"
                                                                accept="image/*,video/*,audio/*,.mp4,.png,.jpg,.pdf">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-primary btn-sm" id="edit-add-media">
                                                <i class="ti ti-plus me-1"></i>Add Another Media
                                            </button>
                                            <p class="text-muted mt-2 mb-0">
                                                <small><i class="ti ti-info-circle me-1"></i>You can add multiple media
                                                    files and assign them to different screens</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="edit-form-alert" class="col-12" style="display: none;">
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Schedule updated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>
                            <div id="edit-upload-progress-container" class="col-12" style="display: none;">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Uploading Files...</h6>
                                        <div class="progress mb-2">
                                            <div id="edit-upload-progress-bar"
                                                class="progress-bar progress-bar-striped progress-bar-animated"
                                                role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                                                aria-valuemax="100">
                                                <span id="edit-upload-progress-text">0%</span>
                                            </div>
                                        </div>
                                        <small id="edit-upload-status-text" class="text-muted">Preparing upload...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" data-bs-dismiss="offcanvas"
                                class="btn btn-sm btn-light me-2">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Update Schedule</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/chunked-upload.js') }}" type="text/javascript"></script>
    <script>
        // Pass schedule permissions to JavaScript
        window.schedulePermissions = @json(\App\Helpers\PermissionHelper::getModulePermissions('schedule'));
    </script>
    <script src="{{ asset('assets/js/datatable/schedule-datatable.js') }}" type="text/javascript"></script>
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #d1d3e2 !important;
            border-radius: 0.375rem !important;
            padding: 0.375rem 0.75rem !important;
        }

        /* Enhanced Progress Bar Styling */
        .progress {
            height: 25px !important;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            border-radius: 15px !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden !important;
            position: relative !important;
        }

        .progress-bar {
            background: linear-gradient(135deg, #28a745 0%, #20c997 50%, #17a2b8 100%) !important;
            border-radius: 15px !important;
            transition: width 0.6s ease-in-out !important;
            position: relative !important;
            overflow: hidden !important;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3) !important;
        }

        .progress-bar::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.3) 50%, transparent 100%) !important;
            animation: shimmer 2s infinite !important;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0.7;
            }
        }

        .progress-text {
            font-weight: 600 !important;
            color: #495057 !important;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8) !important;
        }

        .progress-status {
            color: #6c757d !important;
            font-size: 0.9em !important;
            font-weight: 500 !important;
        }

        /* Upload container styling */
        #upload-progress-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 10px !important;
            padding: 20px !important;
            margin: 15px 0 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }

        #upload-progress-container h3 {
            color: #495057 !important;
            margin-bottom: 15px !important;
            font-weight: 600 !important;
        }

        /* Progress bar container */
        .progress-container {
            margin: 10px 0 !important;
        }

        /* Progress info row */
        .progress-info {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-top: 8px !important;
        }

        /* Success state */
        .progress-bar.progress-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.4) !important;
        }

        /* Warning state */
        .progress-bar.progress-warning {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.4) !important;
        }

        /* Error state */
        .progress-bar.progress-error {
            background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%) !important;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4) !important;
            background-color: #fff !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            padding-left: 0 !important;
            padding-right: 20px !important;
            color: #495057 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, .25) !important;
        }

        .select2-dropdown {
            border: 1px solid #d1d3e2 !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
        }

        /* Progress Bar Styles */
        #upload-progress-container,
        #edit-upload-progress-container {
            margin-top: 1rem;
        }

        #upload-progress-container .card,
        #edit-upload-progress-container .card {
            border: 1px solid #e3e6f0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        #upload-progress-container .card-title,
        #edit-upload-progress-container .card-title {
            color: #5a5c69;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .progress {
            height: 1.5rem;
            background-color: #eaecf4;
            border-radius: 0.35rem;
        }

        .progress-bar {
            background: linear-gradient(45deg, #4e73df, #36b9cc);
            border-radius: 0.35rem;
            transition: width 0.3s ease;
        }

        .progress-bar-striped {
            background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
            background-size: 1rem 1rem;
        }

        .progress-bar-animated {
            animation: progress-bar-stripes 1s linear infinite;
        }

        @keyframes progress-bar-stripes {
            0% {
                background-position-x: 1rem;
            }
        }

        #upload-progress-text,
        #edit-upload-progress-text {
            color: white;
            font-weight: 600;
            font-size: 0.75rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        #upload-status-text,
        #edit-upload-status-text {
            font-size: 0.75rem;
            color: #858796;
        }
    </style>
@endpush
