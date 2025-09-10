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
                    <h4 class="mb-1">Companies</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Companies</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    @if (\App\Helpers\PermissionHelper::canExport('company'))
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
                    @if (\App\Helpers\PermissionHelper::canCreate('company'))
                        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvas_add"><i class="ti ti-square-rounded-plus-filled me-1"></i>Add
                            Company</a>
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
                                                                    class="form-control form-control-md company-filter"
                                                                    placeholder="Search" data-column="name">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Industry Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseIndustry" aria-expanded="false"
                                                        aria-controls="collapseIndustry">Industry</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseIndustry" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md company-filter"
                                                                    placeholder="Search" data-column="industry">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Website Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseWebsite" aria-expanded="false"
                                                        aria-controls="collapseWebsite">Website</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseWebsite" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md company-filter"
                                                                    placeholder="Search" data-column="website">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Email Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseEmail" aria-expanded="false"
                                                        aria-controls="collapseEmail">Email</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseEmail" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md company-filter"
                                                                    placeholder="Search" data-column="email">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Phone Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapsePhone" aria-expanded="false"
                                                        aria-controls="collapsePhone">Phone</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapsePhone" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md company-filter"
                                                                    placeholder="Search" data-column="phone">
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
                                                                    type="checkbox" id="status-active" value="active"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-active">
                                                                    Active
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-inactive" value="inactive"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-inactive">
                                                                    Inactive
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-block" value="block"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-block">
                                                                    Block
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-delete" value="delete"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-delete">
                                                                    Delete
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#Status" aria-expanded="false"
                                                        aria-controls="Status">Status</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="Status" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-active-2" value="active"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-active-2">
                                                                    Active
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-inactive-2"
                                                                    value="inactive" data-column="status">
                                                                <label class="form-check-label" for="status-inactive-2">
                                                                    Inactive
                                                                </label>
                                                            </div>
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-block-2" value="block"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-block-2">
                                                                    Block
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-delete-2" value="delete"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-delete-2">
                                                                    Delete
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="d-flex align-items-center gap-2">
                                            <a href="javascript:void(0);" class="btn btn-outline-light w-100">Reset</a>
                                            <a href="contacts-list.html" class="btn btn-primary w-100">Filter</a>
                                        </div> --}}
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
                                                    <span>Industry</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="industry">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Website</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="website">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Email</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="email">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Phone</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="phone">
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
                                                    <span>Addresses</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked
                                                        data-column="addresses_count">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Contacts</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked
                                                        data-column="contacts_count">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Notes</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="notes_count">
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

                    <!-- Company List -->
                    <div class="data-loading" style="display: none;">
                        <div class="d-flex justify-content-center align-items-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="ms-2">Loading data...</span>
                        </div>
                    </div>
                    <div class="table-responsive custom-table">
                        <table class="table table-nowrap" id="companieslist">
                            <thead class="table-light">
                                <tr>
                                    <th>name</th>
                                    <th>industry</th>
                                    <th>website</th>
                                    <th>email</th>
                                    <th>phone</th>
                                    <th>locations_count</th>
                                    <th>addresses_count</th>
                                    <th>contacts_count</th>
                                    <th>notes_count</th>
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
                    <!-- /Company List -->
                </div>
            </div>
            <!-- end card -->
        </div>
    </div>

    <!-- Start Add Company -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_add">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Add Company</h5>
            <button type="button"
                class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle"
                data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body">
            <div class="card">
                <div class="card-body">
                    <form id="create-company-form" method="POST" action="{{ route('company.store') }}">
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
                                                    <label class="form-label">Company Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Industry</label>
                                                    <input type="text" class="form-control" name="industry">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Website</label>
                                                    <input type="url" class="form-control" name="website">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" class="form-control" name="email">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Phone</label>
                                                    <input type="text" class="form-control" name="phone">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" name="status">
                                                        <option value="delete">Delete</option>
                                                        <option value="active">Active</option>
                                                        <option value="inactive">Inactive</option>
                                                        <option value="block">Block</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Location --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#location">
                                        <span class="avatar avatar-md rounded me-1"><i
                                                class="ti ti-map-pin-cog"></i></span>
                                        Location Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="location" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Locations</label>
                                                    <select class="select2 form-control select2-multiple"
                                                        name="location_ids[]" data-toggle="select2" multiple="multiple"
                                                        data-placeholder="Choose locations...">
                                                        <option value="">Select locations...</option>
                                                        @foreach (\App\Models\Location::where('status', 1)->get() as $location)
                                                            <option value="{{ $location->id }}">{{ $location->name }} -
                                                                {{ $location->city }}, {{ $location->country }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Address --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#address">
                                        <span class="avatar avatar-md rounded me-1"><i
                                                class="ti ti-map-pin-cog"></i></span>
                                        Address Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="address" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Addresses</label>
                                                    <div id="addresses-container">
                                                        <div class="address-item border rounded p-3 mb-2">
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <select class="form-select" name="addresses[0][type]"
                                                                        required>
                                                                        <option value="">Select Type</option>
                                                                        <option value="head office">Head Office</option>
                                                                        <option value="branch">Branch</option>
                                                                        <option value="office">Office</option>
                                                                        <option value="warehouse">Warehouse</option>
                                                                        <option value="factory">Factory</option>
                                                                        <option value="store">Store</option>
                                                                        <option value="billing">Billing</option>
                                                                        <option value="shipping">Shipping</option>
                                                                        <option value="home">Home</option>
                                                                        <option value="mailing">Mailing</option>
                                                                        <option value="corporate">Corporate</option>
                                                                        <option value="other">Other</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-9">
                                                                    <input type="text" class="form-control"
                                                                        name="addresses[0][address]"
                                                                        placeholder="Address">
                                                                </div>
                                                            </div>
                                                            <div class="row mt-2">
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        name="addresses[0][city]" placeholder="City">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        name="addresses[0][state]" placeholder="State">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        name="addresses[0][country]"
                                                                        placeholder="Country">
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control"
                                                                        name="addresses[0][zip_code]"
                                                                        placeholder="Zip Code">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="addAddress()">
                                                        <i class="ti ti-plus me-1"></i>Add Address
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Contacts --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#contacts">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-user-plus"></i></span>
                                        Contacts Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="contacts" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Contacts</label>
                                                    <div id="contacts-container">
                                                        <div class="contact-item border rounded p-3 mb-2">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control"
                                                                        name="contacts[0][name]"
                                                                        placeholder="Contact Name">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <input type="email" class="form-control"
                                                                        name="contacts[0][email]" placeholder="Email">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <input type="text" class="form-control"
                                                                        name="contacts[0][phone]" placeholder="Phone">
                                                                </div>
                                                            </div>
                                                            <div class="row mt-2">
                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control"
                                                                        name="contacts[0][designation]"
                                                                        placeholder="Designation">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="contacts[0][is_primary]" value="1">
                                                                        <label class="form-check-label">Primary
                                                                            Contact</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="addContact()">
                                                        <i class="ti ti-plus me-1"></i>Add Contact
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#notes">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-note"></i></span>
                                        Notes Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="notes" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <div id="notes-container">
                                                        <div class="note-item border rounded p-3 mb-2">
                                                            <div class="row">
                                                                <div class="col-md-9">
                                                                    <textarea class="form-control" name="notes[0][note]" rows="3" placeholder="Note content"></textarea>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select class="form-select" name="notes[0][status]">
                                                                        <option value="0">Delete</option>
                                                                        <option value="1">Active</option>
                                                                        <option value="2">Inactive</option>
                                                                        <option value="3">Block</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="addNote()">
                                                        <i class="ti ti-plus me-1"></i>Add Note
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="create-form-alert" class="col-12" style="display: none;">
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Company created successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" data-bs-dismiss="offcanvas"
                                class="btn btn-sm btn-light me-2">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Create Company</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat Edit Company --}}
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_edit"
        aria-labelledby="offcanvas_edit_label">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas_edit_label">Edit Company</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="card">
                <div class="card-body">
                    <form id="edit-company-form" method="POST">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="from_index" value="1">
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
                                                    <label class="form-label">Company Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name"
                                                        id="edit-name" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Industry</label>
                                                    <input type="text" class="form-control" name="industry"
                                                        id="edit-industry">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Website</label>
                                                    <input type="url" class="form-control" name="website"
                                                        id="edit-website">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" class="form-control" name="email"
                                                        id="edit-email">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Phone</label>
                                                    <input type="text" class="form-control" name="phone"
                                                        id="edit-phone">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" name="status" id="edit-status">
                                                        <option value="delete">Delete</option>
                                                        <option value="active">Active</option>
                                                        <option value="inactive">Inactive</option>
                                                        <option value="block">Block</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Location --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#location">
                                        <span class="avatar avatar-md rounded me-1"><i
                                                class="ti ti-map-pin-cog"></i></span>
                                        Location Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="location" data-bs-parent="#main_accordion">
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
                                                            <option value="{{ $location->id }}">{{ $location->name }} -
                                                                {{ $location->city }}, {{ $location->country }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Address --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#address">
                                        <span class="avatar avatar-md rounded me-1"><i
                                                class="ti ti-map-pin-cog"></i></span>
                                        Address Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="address" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Addresses</label>
                                                    <div id="edit-addresses-container">
                                                        <!-- Addresses will be populated via JavaScript -->
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="addEditAddress()">
                                                        <i class="ti ti-plus me-1"></i>Add Address
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Contacts --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#contacts">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-user-plus"></i></span>
                                        Contacts Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="contacts" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Contacts</label>
                                                    <div id="edit-contacts-container">
                                                        <!-- Contacts will be populated via JavaScript -->
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="addEditContact()">
                                                        <i class="ti ti-plus me-1"></i>Add Contact
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#notes">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-note"></i></span>
                                        Notes Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="notes" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <div id="edit-notes-container">
                                                        <!-- Notes will be populated via JavaScript -->
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="addEditNote()">
                                                        <i class="ti ti-plus me-1"></i>Add Note
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="edit-form-alert" class="col-12" style="display: none;">
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    Company updated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>

                        </div>
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" data-bs-dismiss="offcanvas"
                                class="btn btn-sm btn-light me-2">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Update Company</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Location Modal -->
    <div class="modal fade" id="delete_location" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this location?</p>
                    <p>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="delete-location-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Edit Location Offcanvas -->

    <!-- Delete Location Modal -->
    <div class="modal fade" id="delete_location" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this location?</p>
                    <p>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="delete-location-form" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Detail Modal -->
    <div class="modal fade" id="location_detail" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Location Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Name:</label>
                            <p id="detail-name"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Email:</label>
                            <p id="detail-email"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Address:</label>
                            <p id="detail-address"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">City:</label>
                            <p id="detail-city"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">State:</label>
                            <p id="detail-state"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Country:</label>
                            <p id="detail-country"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Zip Code:</label>
                            <p id="detail-zip_code"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Status:</label>
                            <p id="detail-status"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Created At:</label>
                            <p id="detail-created_at"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Select2 CSS and JS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>

    <!-- Company DataTable JS -->
    <script>
        // Pass company permissions to JavaScript
        window.companyPermissions = @json(\App\Helpers\PermissionHelper::getModulePermissions('company'));
    </script>
    <script src="{{ asset('assets/js/datatable/company-datatable.js') }}" type="text/javascript"></script>

    <style>
        .highlight-row {
            animation: highlight 3s;
        }

        @keyframes highlight {
            0% {
                background-color: rgba(255, 255, 140, 0.5);
            }

            100% {
                background-color: transparent;
            }
        }

        /* Alert styling improvements */
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .alert-info .btn-close {
            color: #0c5460;
        }

        /* Form loading state */
        .form-loading {
            opacity: 0.6;
            pointer-events: none;
        }

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
