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
                    <h4 class="mb-1">Areas</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Areas</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    @if (\App\Helpers\PermissionHelper::canExport('area'))
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-outline-primary px-2 shadow"
                                data-bs-toggle="dropdown"><i class="ti ti-package-export me-2"></i>Export</a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <ul>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                class="ti ti-file-type-pdf me-1"></i>Export
                                            as PDF</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item"><i
                                                class="ti ti-file-type-xls me-1"></i>Export
                                            as Excel</a>
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

            <!-- start card -->
            <div class="card border-0 rounded-0">
                <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="input-icon input-icon-start position-relative">
                        <span class="input-icon-addon text-dark"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search">
                    </div>
                    @if (\App\Helpers\PermissionHelper::canCreate('area'))
                        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvas_add"><i class="ti ti-square-rounded-plus-filled me-1"></i>Add
                            Area</a>
                    @endif
                </div>
                <div class="card-body">
                    <!-- table header -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle btn btn-outline-light px-2 shadow"
                                    data-bs-toggle="dropdown"><i class="ti ti-sort-ascending-2 me-2"></i>Sort By</a>
                                <div class="dropdown-menu">
                                    <ul>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item sort-option"
                                                data-sort="newest">Newest</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item sort-option"
                                                data-sort="oldest">Oldest</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div id="reportrange" class="reportrange-picker d-flex align-items-center shadow">
                                <i class="ti ti-calendar-due text-dark fs-14 me-1"></i><span
                                    class="reportrange-picker-field">9 Jun
                                    25 - 9 Jun 25</span>
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
                                            <!-- Name Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseTwo" aria-expanded="true"
                                                        aria-controls="collapseTwo">Name</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse show"
                                                    id="collapseTwo" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md area-filter"
                                                                    placeholder="Search" data-column="name">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Description Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseDescription" aria-expanded="false"
                                                        aria-controls="collapseDescription">Description</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseDescription" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md area-filter"
                                                                    placeholder="Search" data-column="description">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Code Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseCode" aria-expanded="false"
                                                        aria-controls="collapseCode">Code</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseCode" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md area-filter"
                                                                    placeholder="Search" data-column="code">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Status Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseStatus" aria-expanded="false"
                                                        aria-controls="collapseStatus">Status</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseStatus" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-activate" value="1"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-activate">
                                                                    Activate
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-inactive" value="2"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-inactive">
                                                                    Inactive
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-block" value="3"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-block">
                                                                    Block
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-delete" value="0"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-delete">
                                                                    Delete
                                                                </label>
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
                                                        type="checkbox" role="switch" checked data-column="name">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Description</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="description">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Code</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="code">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Locations</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked
                                                        data-column="locations_count">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Companies</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked
                                                        data-column="companies_count">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Created By</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="created_by">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Updated By</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="updated_by">
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
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Updated At</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="updated_at">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Status</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="status">
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
                                <a href="contacts-list.html" class="btn btn-sm p-1 border-0 fs-14 active"><i
                                        class="ti ti-list-tree"></i></a>
                                <a href="contacts.html" class="flex-shrink-0 btn btn-sm p-1 border-0 ms-1 fs-14"><i
                                        class="ti ti-grid-dots"></i></a>
                            </div>
                        </div>
                    </div>
                    <!-- table header -->

                    <!-- Area List -->
                    <div class="data-loading" style="display: none;">
                        <div class="d-flex justify-content-center align-items-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="ms-2">Loading data...</span>
                        </div>
                    </div>
                    <div class="table-responsive custom-table">
                        <table class="table table-nowrap" id="areaslist">
                            <thead class="table-light">
                                <tr>
                                    <th>name</th>
                                    <th>description</th>
                                    <th>code</th>
                                    <th>locations_count</th>
                                    <th>companies_count</th>
                                    <th>created_by</th>
                                    <th>updated_by</th>
                                    <th>created_at</th>
                                    <th>updated_at</th>
                                    <th>status</th>
                                    <th class="text-end no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div id="error-container" class="alert alert-danger mt-3" style="display: none;">
                        <strong>Error!</strong> <span id="error-message">Unable to load data.</span>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="datatable-length"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="datatable-paginate"></div>
                        </div>
                    </div>
                    <!-- /Area List -->
                </div>
            </div>
            <!-- end card -->
        </div>
    </div>

    <!-- Start Add Area -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_add">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Add Area</h5>
            <button type="button"
                class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle"
                data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body">
            <div class="card">
                <div class="card-body">
                    <form id="create-area-form" method="POST" action="{{ route('area.store') }}">
                        @csrf
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
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Area Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Code</label>
                                                    <input type="text" class="form-control" name="code">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" name="description" rows="3"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" name="status">
                                                        <option value="1">Activate</option>
                                                        <option value="2">Inactive</option>
                                                        <option value="3">Block</option>
                                                        <option value="0">Delete</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Locations -->
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#locations">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-map-pin"></i></span>
                                        Locations
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="locations" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="mb-3">
                                            <label class="form-label">Select Locations</label>
                                            <select class="form-select select2-multiple" name="location_ids[]" multiple>
                                                @foreach (\App\Models\Location::all() as $location)
                                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Companies -->
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#companies">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-building"></i></span>
                                        Companies
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="companies" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="mb-3">
                                            <label class="form-label">Select Companies</label>
                                            <select class="form-select select2-multiple" name="company_ids[]" multiple>
                                                @foreach (\App\Models\Company::all() as $company)
                                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alert for form submission -->
                        <div id="create-form-alert" class="alert mt-3" style="display: none;"></div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Create Area</button>
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="offcanvas">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Add Area -->

    <!-- Start Edit Area -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_edit">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Edit Area</h5>
            <button type="button"
                class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle"
                data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body">
            <div class="card">
                <div class="card-body">
                    <form id="edit-area-form" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="accordion accordion-bordered" id="edit_main_accordion">
                            <!-- Basic Info -->
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#edit_basic">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-user-plus"></i></span>
                                        Basic Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse show" id="edit_basic"
                                    data-bs-parent="#edit_main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Area Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="edit-name"
                                                        name="name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Code</label>
                                                    <input type="text" class="form-control" id="edit-code"
                                                        name="code">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" id="edit-status" name="status">
                                                        <option value="1">Activate</option>
                                                        <option value="2">Inactive</option>
                                                        <option value="3">Block</option>
                                                        <option value="0">Delete</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Locations -->
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#edit_locations">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-map-pin"></i></span>
                                        Locations
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="edit_locations"
                                    data-bs-parent="#edit_main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="mb-3">
                                            <label class="form-label">Select Locations</label>
                                            <select class="form-select select2-multiple" id="edit-location_ids"
                                                name="location_ids[]" multiple>
                                                @foreach (\App\Models\Location::all() as $location)
                                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Companies -->
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#edit_companies">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-building"></i></span>
                                        Companies
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="edit_companies"
                                    data-bs-parent="#edit_main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="mb-3">
                                            <label class="form-label">Select Companies</label>
                                            <select class="form-select select2-multiple" id="edit-company_ids"
                                                name="company_ids[]" multiple>
                                                @foreach (\App\Models\Company::all() as $company)
                                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alert for form submission -->
                        <div id="edit-form-alert" class="alert mt-3" style="display: none;"></div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Update Area</button>
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="offcanvas">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Edit Area -->

    <!-- Delete Area Modal -->
    <div class="modal fade" id="delete_area" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this area?</p>
                    <p>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="delete-area-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete Area Modal -->
@endsection

@push('js')
    <!-- Select2 CSS and JS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>

    <!-- Area DataTable JS -->
    <script>
        // Pass area permissions to JavaScript
        window.areaPermissions = @json(\App\Helpers\PermissionHelper::getModulePermissions('area'));
    </script>
    <script src="{{ asset('assets/js/datatable/area-datatable.js') }}" type="text/javascript"></script>

    <style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #d1d3e2 !important;
            border-radius: 0.375rem !important;
            min-height: 38px !important;
            padding: 0.375rem 0.75rem !important;
            background-color: #fff !important;
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

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: relative !important;
            right: auto !important;
            top: auto !important;
            transform: none !important;
            color: #6c757d !important;
            font-weight: bold !important;
            font-size: 1rem !important;
            line-height: 1 !important;
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
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #dc3545 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:before {
            content: "×" !important;
            font-size: 1.125rem !important;
            line-height: 1 !important;
        }

        /* Remove any duplicate × symbols */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:after {
            content: "" !important;
        }

        /* Hide any default Select2 remove button text */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            text-indent: -9999px !important;
            overflow: hidden !important;
        }

        /* Show only our custom × */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:before {
            content: "×" !important;
            font-size: 1.125rem !important;
            line-height: 1 !important;
            text-indent: 0 !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            color: #6c757d !important;
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

        /* Ensure proper spacing */
        .select2-container--default .select2-selection--multiple .select2-selection__rendered li {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Remove default Select2 styling that might interfere */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            float: none !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            float: none !important;
        }

        /* Additional styling for better appearance */
        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            min-height: 20px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        /* Force single × symbol - override all default Select2 styling */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            font-size: 0 !important;
            line-height: 0 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:before {
            content: "×" !important;
            font-size: 1.125rem !important;
            line-height: 1 !important;
            color: #6c757d !important;
            font-weight: bold !important;
        }

        /* Remove any other potential × symbols */
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:after,
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove span,
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove i {
            display: none !important;
            content: "" !important;
        }
    </style>

    <script>
        $(document).ready(function() {
            console.log('jQuery loaded:', typeof $ !== 'undefined');
            console.log('Select2 loaded:', typeof $.fn.select2 !== 'undefined');
            console.log('Initializing Select2...');

            // Initialize Select2 with proper configuration
            $('.select2-multiple').each(function() {
                console.log('Initializing Select2 for:', this);
                $(this).select2({
                    theme: 'default',
                    width: '100%',
                    placeholder: 'Choose locations...',
                    allowClear: true,
                    closeOnSelect: false,
                    tags: false,
                    tokenSeparators: [',', ' '],
                    language: {
                        noResults: function() {
                            return "No locations found";
                        },
                        searching: function() {
                            return "Searching...";
                        }
                    },
                    templateResult: function(data) {
                        if (data.loading) return data.text;
                        return data.text;
                    },
                    templateSelection: function(data) {
                        return data.text;
                    }
                });
            });

            // Re-initialize Select2 after dynamic content is added
            $(document).on('shown.bs.offcanvas', function() {
                console.log('Offcanvas shown, re-initializing Select2...');
                $('.select2-multiple').each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        console.log('Re-initializing Select2 for:', this);
                        $(this).select2({
                            theme: 'default',
                            width: '100%',
                            placeholder: 'Choose locations...',
                            allowClear: true,
                            closeOnSelect: false,
                            tags: false,
                            tokenSeparators: [',', ' ']
                        });
                    }
                });
            });

            // Force re-initialization after a short delay
            setTimeout(function() {
                console.log('Force re-initializing Select2...');
                $('.select2-multiple').each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2({
                            theme: 'default',
                            width: '100%',
                            placeholder: 'Choose locations...',
                            allowClear: true,
                            closeOnSelect: false,
                            tags: false,
                            tokenSeparators: [',', ' ']
                        });
                    }
                });
            }, 1000);
        });
    </script>
@endpush
