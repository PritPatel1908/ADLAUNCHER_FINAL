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
                    <h4 class="mb-1">Locations</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Locations</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    @if (\App\Helpers\PermissionHelper::canExport('location'))
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
                    @if (\App\Helpers\PermissionHelper::canCreate('location'))
                        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvas_add"><i class="ti ti-square-rounded-plus-filled me-1"></i>Add
                            Location</a>
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
                                                                    class="form-control form-control-md location-filter"
                                                                    placeholder="Search" data-column="name">
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
                                                                    class="form-control form-control-md location-filter"
                                                                    placeholder="Search" data-column="email">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Address Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseAddress" aria-expanded="false"
                                                        aria-controls="collapseAddress">Address</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseAddress" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md location-filter"
                                                                    placeholder="Search" data-column="address">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- City Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseCity" aria-expanded="false"
                                                        aria-controls="collapseCity">City</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseCity" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md location-filter"
                                                                    placeholder="Search" data-column="city">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- State Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseState" aria-expanded="false"
                                                        aria-controls="collapseState">State</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseState" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md location-filter"
                                                                    placeholder="Search" data-column="state">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Country Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseCountry" aria-expanded="false"
                                                        aria-controls="collapseCountry">Country</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseCountry" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md location-filter"
                                                                    placeholder="Search" data-column="country">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Zip Code Filter -->
                                            <div class="filter-set-content">
                                                <div class="filter-set-content-head">
                                                    <a href="#" class="collapsed" data-bs-toggle="collapse"
                                                        data-bs-target="#collapseZipCode" aria-expanded="false"
                                                        aria-controls="collapseZipCode">Zip Code</a>
                                                </div>
                                                <div class="filter-set-contents accordion-collapse collapse"
                                                    id="collapseZipCode" data-bs-parent="#accordionExample">
                                                    <div
                                                        class="filter-content-list bg-light rounded border p-2 shadow mt-2">
                                                        <div class="mb-2">
                                                            <div class="input-icon-start input-icon position-relative">
                                                                <span class="input-icon-addon fs-12">
                                                                    <i class="ti ti-search"></i>
                                                                </span>
                                                                <input type="text"
                                                                    class="form-control form-control-md location-filter"
                                                                    placeholder="Search" data-column="zip_code">
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
                                                                    type="checkbox" id="status-blocked" value="blocked"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-blocked">
                                                                    Blocked
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input status-filter"
                                                                    type="checkbox" id="status-deleted" value="deleted"
                                                                    data-column="status">
                                                                <label class="form-check-label" for="status-deleted">
                                                                    Deleted
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
                                                    <span>Address</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="address">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>City</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="city">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>State</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="state">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Country</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="country">
                                                </label>
                                            </div>
                                        </li>
                                        <li class="gap-1 d-flex align-items-center mb-2">
                                            <i class="ti ti-columns me-1"></i>
                                            <div class="form-check form-switch w-100 ps-0">
                                                <label class="form-check-label d-flex align-items-center gap-2 w-100">
                                                    <span>Zip Code</span>
                                                    <input class="form-check-input column-visibility-toggle ms-auto"
                                                        type="checkbox" role="switch" checked data-column="zip_code">
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

                    <!-- Location List -->
                    <div class="data-loading" style="display: none;">
                        <div class="d-flex justify-content-center align-items-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="ms-2">Loading data...</span>
                        </div>
                    </div>
                    <div class="table-responsive custom-table">
                        <table class="table table-nowrap" id="contactslist">
                            <thead class="table-light">
                                <tr>
                                    <th>name</th>
                                    <th>email</th>
                                    <th>address</th>
                                    <th>city</th>
                                    <th>state</th>
                                    <th>country</th>
                                    <th>zip_code</th>
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
                    <!-- /Location List -->
                </div>
            </div>
            <!-- end card -->
        </div>
    </div>

    <!-- Start Add Location -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_add">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Add Location</h5>
            <button type="button"
                class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle"
                data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body">
            <div class="card">
                <div class="card-body">
                    <form id="create-location-form" method="POST" action="{{ route('location.store') }}">
                        @csrf
                        <div class="accordion accordion-bordered" id="main_accordion2">
                            <!-- Basic Info -->
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#basic2">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-user-plus"></i></span>
                                        Basic Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse show" id="basic2"
                                    data-bs-parent="#main_accordion2">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Location Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name" required>
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
                                                    <label class="form-label">Address</label>
                                                    <input type="text" class="form-control" name="address">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">City</label>
                                                    <input type="text" class="form-control" name="city">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">State</label>
                                                    <input type="text" class="form-control" name="state">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Country</label>
                                                    <input type="text" class="form-control" name="country">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Zip Code</label>
                                                    <input type="text" class="form-control" name="zip_code">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" name="status">
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                        <option value="2">Blocked</option>
                                                        <option value="3">Deleted</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div id="create-form-alert" class="col-12" style="display: none;">
                                                <div class="alert alert-success alert-dismissible fade show"
                                                    role="alert">
                                                    Location created successfully!
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                        aria-label="Close"></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-end">
                            <button type="button" data-bs-dismiss="offcanvas"
                                class="btn btn-sm btn-light me-2">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Create Location</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_edit" aria-labelledby="offcanvas_edit_label">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas_edit_label">Edit Location</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="row">
                <div class="col-12">
                    <form id="edit-location-form" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="accordion accordion-bordered" id="main_accordion2">
                            <!-- Basic Info -->
                            <div class="accordion-item rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded"
                                        data-bs-toggle="collapse" data-bs-target="#basic2">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-user-plus"></i></span>
                                        Basic Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse show" id="basic2"
                                    data-bs-parent="#main_accordion2">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Location Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name"
                                                        id="edit-name" required>
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
                                                    <label class="form-label">Address</label>
                                                    <input type="text" class="form-control" name="address"
                                                        id="edit-address">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">City</label>
                                                    <input type="text" class="form-control" name="city"
                                                        id="edit-city">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">State</label>
                                                    <input type="text" class="form-control" name="state"
                                                        id="edit-state">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Country</label>
                                                    <input type="text" class="form-control" name="country"
                                                        id="edit-country">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Zip Code</label>
                                                    <input type="text" class="form-control" name="zip_code"
                                                        id="edit-zip_code">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" name="status" id="edit-status">
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                        <option value="2">Blocked</option>
                                                        <option value="3">Deleted</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div id="edit-form-alert" class="col-12" style="display: none;">
                                                <div class="alert alert-success alert-dismissible fade show"
                                                    role="alert">
                                                    Location updated successfully!
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                        aria-label="Close"></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-end">
                                            <button type="button" data-bs-dismiss="offcanvas"
                                                class="btn btn-sm btn-light me-2">Cancel</button>
                                            <button type="submit" class="btn btn-sm btn-primary">Update Location</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                    <button type="button" id="confirm-delete-btn" class="btn btn-danger">Delete</button>
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
    <!-- Removed duplicate DataTable initialization - now using only location-datatable.js -->
    <script>
        // Pass location permissions to JavaScript
        window.locationPermissions = @json(\App\Helpers\PermissionHelper::getModulePermissions('location'));
        // Absolute URL for datatable endpoint to avoid 404s due to base path issues
        window.locationsDataUrl = "{{ route('locations.data') }}";
    </script>
    <script src="{{ asset('assets/js/datatable/location-datatable.js') }}" type="text/javascript"></script>

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
    </style>

    {{-- <script>
    $(document).ready(function() {
            // Setup AJAX CSRF token for all requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            // Reference to existing DataTable instance
            var locationsTable = $('#contactslist').DataTable();

            // Handle search functionality
            $('.input-icon input[type="text"]').on('keyup', function() {
                locationsTable.search(this.value).draw();
            });

            // Create location form submission is now handled in location-datatable.js

            // DataTable is now reloaded via ajax.reload() in location-datatable.js

            // Handle edit location form submit
            // $('#edit-location-form').on('submit', function(e) {
            //     e.preventDefault();
            //     var form = $(this);
            //     var url = form.attr('action');
            //     var locationId = form.data('location-id');

            //     $.ajax({
            //         url: url,
            //         type: 'POST',
            //         data: form.serialize(),
            //         dataType: 'json',
            //         success: function(response) {
            //             // Show success message using notification function
            //             showNotification(response.message || 'Location updated successfully',
            //                 'success');

            //             // Close the modal
            //             $('#edit_location').modal('hide');

            //             if (response.location) {
            //                 // Update the row in the table without page reload
            //                 updateLocationInTable(response.location);
            //             } else {
            //                 // If no location data returned, reload after delay
            //                 setTimeout(function() {
            //                     location.reload();
            //                 }, 500);
            //             }
            //         },
            //         error: function(xhr) {
            //             console.error('Error updating location');
            //             showNotification('Error updating location. Please try again.', 'error');
            //         }
            //     });
            // });

            // // Function to update a location in the table without page reload
            // function updateLocationInTable(location) {
            //     // Find the row with the location ID
            //     var rows = locationsTable.rows().nodes();
            //     var rowToUpdate = null;

            //     $(rows).each(function() {
            //         var actionCell = $(this).find('td:last-child');
            //         var locationIdInRow = actionCell.find('a[data-location-id]').data('location-id');

            //         if (locationIdInRow == location.id) {
            //             rowToUpdate = this;
            //             return false; // Break the loop
            //         }
            //     });

            //     if (rowToUpdate) {
            //         var statusBadge = location.status == 1 ?
            //             '<span class="badge bg-success-light text-success">Active</span>' :
            //             '<span class="badge bg-danger-light text-danger">Inactive</span>';

            //         // Update the row data
            //         var rowData = [
            //             $(rowToUpdate).find('td:eq(0)').html(), // Keep checkbox
            //             '<div class="avatar avatar-sm rounded-circle"><span class="avatar-text bg-primary-light text-primary">' +
            //             location.name.charAt(0) + '</span></div>',
            //             location.name,
            //             location.email || 'N/A',
            //             '<span class="badge bg-primary-light text-primary">Location</span>',
            //             '<div class="d-flex align-items-center gap-1"><span>' + (location.city || 'N/A') +
            //             ', ' + (location.country || 'N/A') + '</span></div>',
            //             '<div class="d-flex align-items-center gap-1"><span>' + (location.state || 'N/A') +
            //             '</span></div>',
            //             '<div class="d-flex align-items-center gap-1"><span>' + (location.address || 'N/A') +
            //             '</span></div>',
            //             statusBadge,
            //             $(rowToUpdate).find('td:eq(9)').html() // Keep action buttons
            //         ];

            //         locationsTable.row(rowToUpdate).data(rowData).draw(false);

            //         // Highlight the updated row
            //         $(rowToUpdate).addClass('highlight-row');
            //         setTimeout(function() {
            //             $(rowToUpdate).removeClass('highlight-row');
            //         }, 3000);
            //     }
            // }

            // Function to show notification
            function showNotification(message, type) {
                var alertClass = 'alert-success';
                if (type === 'error') {
                    alertClass = 'alert-danger';
                }

                var notification = $('<div class="alert ' + alertClass +
                    ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>');

                // Append to the top of the content area
                $('.content').prepend(notification);

                // Auto dismiss after 3 seconds
                setTimeout(function() {
                    notification.alert('close');
                }, 3000);
            }

            // Handle view location details
            $(document).on('click', '[data-bs-target="#location_detail"]', function() {
                var locationId = $(this).data('location-id');

                // Fetch location details via AJAX
                $.ajax({
                    url: '/location/' + locationId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.location) {
                            var location = response.location;

                            // Populate the modal with location details
                            $('#detail-name').text(location.name);
                            $('#detail-email').text(location.email || 'N/A');
                            $('#detail-address').text(location.address || 'N/A');
                            $('#detail-city').text(location.city || 'N/A');
                            $('#detail-state').text(location.state || 'N/A');
                            $('#detail-country').text(location.country || 'N/A');
                            $('#detail-zip_code').text(location.zip_code || 'N/A');
                            $('#detail-status').text(location.status == 1 ? 'Active' :
                                'Inactive');
                            $('#detail-created_at').text(location.created_at);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching location details');
                    }
                });
            });

            // Handle edit location button
            $(document).on('click', '[data-bs-target="#edit_location"]', function(e) {
                // Prevent the default behavior (which would open the modal immediately)
                e.preventDefault();

                var locationId = $(this).data('location-id');

                // Fetch location details via AJAX
                $.ajax({
                    url: '/location/' + locationId + '/edit',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.location) {
                            var location = response.location;

                            // Set the form action URL
                            $('#edit-location-form').attr('action', '/location/' + locationId);
                            $('#edit-location-form').data('location-id', locationId);

                            // Populate the form fields
                            $('#edit-name').val(location.name);
                            $('#edit-email').val(location.email);
                            $('#edit-address').val(location.address);
                            $('#edit-city').val(location.city);
                            $('#edit-state').val(location.state);
                            $('#edit-country').val(location.country);
                            $('#edit-zip_code').val(location.zip_code);
                            $('#edit-status').val(location.status);

                            // Manually open the modal after data is loaded
                            var modalElement = document.getElementById('edit_location');
                            var modal = new bootstrap.Modal(modalElement);
                            modal.show();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching location details for edit');
                        showNotification('Error loading location data. Please try again.', 'error');
                    }
                });
            });

            // Handle delete button
            $(document).on('click', '[data-bs-target="#delete_location"]', function() {
                var locationId = $(this).data('location-id');

                // Set the form action URL
                $('#delete-location-form').attr('action', '/location/' + locationId);
                $('#delete-location-form').data('location-id', locationId);
            });

            // Handle delete form submit
            $('#delete-location-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var locationId = form.data('location-id');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        // Close the modal
                        $('#delete_location').modal('hide');

                        // Remove the row from the table
                        var rows = locationsTable.rows().nodes();
                        $(rows).each(function() {
                            var actionCell = $(this).find('td:last-child');
                            var locationIdInRow = actionCell.find('a[data-location-id]')
                                .data('location-id');

                            if (locationIdInRow == locationId) {
                                locationsTable.row(this).remove().draw(false);
                                return false; // Break the loop
                            }
                        });

                        // Show a success message
                        showNotification('Location deleted successfully', 'success');
                    },
                    error: function(xhr) {
                        console.error('Error deleting location');
                        showNotification('Error deleting location. Please try again.', 'error');
                    }
                });
            });

            // We're now handling the edit functionality directly in the click event handler
            // No need for the show.bs.offcanvas event handler anymore

            // Handle delete button click
            $('#delete_location').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var locationId = button.data('location-id');
                var form = $('#delete-location-form');

                // Set the form action URL
                form.attr('action', '/location/' + locationId);
            });

            // Handle view button click
            $('#location_detail').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var locationId = button.data('location-id');

                // Fetch location data via AJAX
                $.ajax({
                    url: '/location/' + locationId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        var data = response.location || response;
                        $('#detail-name').text(data.name);
                        $('#detail-email').text(data.email || 'N/A');
                        $('#detail-address').text(data.address || 'N/A');
                        $('#detail-city').text(data.city || 'N/A');
                        $('#detail-state').text(data.state || 'N/A');
                        $('#detail-country').text(data.country || 'N/A');
                        $('#detail-zip_code').text(data.zip_code || 'N/A');
                        $('#detail-status').text(data.status == 1 ? 'Active' : 'Inactive');
                        $('#detail-created_at').text(data.created_at);
                    },
                    error: function(xhr) {
                        console.error('Error fetching location data');
                    }
                });
            });
        });
</script> --}}
@endpush

{{-- @push('scripts')
<script>
    $(document).ready(function() {
            // Setup AJAX CSRF token for all requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            // Initialize DataTable for locations list
            var locationsTable = $('#contactslist').DataTable({
                responsive: true,
                ordering: true,
                paging: true,
                searching: true,
                info: true,
                lengthChange: true,
                pageLength: 10,
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                }],
                dom: '<"datatable-length"l><"datatable-paginate"p>'
            });

            // Handle search functionality
            $('.input-icon input[type="text"]').on('keyup', function() {
                locationsTable.search(this.value).draw();
            });

            // Handle create location form submit
            $('#create-location-form').on('submit', function(e) {
                e.preventDefault();

                // Disable submit button to prevent double submission
                var submitBtn = $(this).find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                submitBtn.html('Creating...').prop('disabled', true);

                $.ajax({
                    url: '{{ route('location.store') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        console.log('Success response:', response);

                        // Show success message using notification function
                        showNotification(response.message || 'Location created successfully',
                            'success');

                        // Clear the form
                        $('#create-location-form')[0].reset();

                        // Add the new location to the table without page reload
                        if (response && response.location) {
                            addLocationToTable(response.location);
                        } else {
                            console.log('No location data in response, reloading page');
                            // If no location data returned, reload after delay
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error creating location:', xhr.responseText);
                        // Show error message using notification function
                        showNotification('Error creating location. Please try again.', 'error');
                    },
                    complete: function() {
                        // Re-enable submit button
                        submitBtn.html(originalBtnText).prop('disabled', false);
                    }
                });
            });

            // Function to add a new location to the table without page reload
            function addLocationToTable(location) {
                console.log('Adding location to table:', location);

                // Default status to 1 (Active) if not provided
                if (location.status === undefined) {
                    location.status = 1;
                }

                var statusBadge = location.status == 1 ?
                    '<span class="badge bg-success-light text-success">Active</span>' :
                    '<span class="badge bg-danger-light text-danger">Inactive</span>';

                try {
                    var newRow = locationsTable.row.add([
                        '<div class="form-check form-check-md"><input class="form-check-input" type="checkbox"></div>',
                        '<div class="avatar avatar-sm rounded-circle"><span class="avatar-text bg-primary-light text-primary">' +
                        location.name.charAt(0) + '</span></div>',
                        location.name,
                        location.email || 'N/A',
                        '<span class="badge bg-primary-light text-primary">Location</span>',
                        '<div class="d-flex align-items-center gap-1"><span>' + (location.city || 'N/A') +
                        ', ' + (location.country || 'N/A') + '</span></div>',
                        '<div class="d-flex align-items-center gap-1"><span>' + (location.state || 'N/A') +
                        '</span></div>',
                        '<div class="d-flex align-items-center gap-1"><span>' + (location.address ||
                            'N/A') + '</span></div>',
                        statusBadge,
                        '<div class="dropdown text-end"><a href="javascript:void(0);" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></a><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#location_detail" data-location-id="' +
                        location.id +
                        '"><i class="ti ti-eye me-2"></i>View</a></li><li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit_location" data-location-id="' +
                        location.id +
                        '"><i class="ti ti-pencil me-2"></i>Edit</a></li><li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_location" data-location-id="' +
                        location.id + '"><i class="ti ti-trash me-2"></i>Delete</a></li></ul></div>'
                    ]).draw(false).node();

                    // Close the offcanvas
                    $('#offcanvas_add').offcanvas('hide');

                    // Highlight the new row
                    $(newRow).addClass('highlight-row');
                    setTimeout(function() {
                        $(newRow).removeClass('highlight-row');
                    }, 3000);

                    console.log('Location added successfully to table');
                } catch (error) {
                    console.error('Error adding location to table:', error);
                    // If there's an error adding to the table, reload the page
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                }
            }

            // Handle edit location form submit
            $('#edit-location-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var locationId = form.data('location-id');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        // Show success message using notification function
                        showNotification(response.message || 'Location updated successfully',
                            'success');

                        // Close the offcanvas
                        $('#offcanvas_edit').offcanvas('hide');

                        if (response.location) {
                            // Update the row in the table without page reload
                            updateLocationInTable(response.location);
                        } else {
                            // If no location data returned, reload after delay
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error updating location');
                        showNotification('Error updating location. Please try again.', 'error');
                    }
                });
            });

            // Function to update a location in the table without page reload
            function updateLocationInTable(location) {
                // Find the row with the location ID
                var rows = locationsTable.rows().nodes();
                var rowToUpdate = null;

                $(rows).each(function() {
                    var actionCell = $(this).find('td:last-child');
                    var locationIdInRow = actionCell.find('a[data-location-id]').data('location-id');

                    if (locationIdInRow == location.id) {
                        rowToUpdate = this;
                        return false; // Break the loop
                    }
                });

                if (rowToUpdate) {
                    var statusBadge = location.status == 1 ?
                        '<span class="badge bg-success-light text-success">Active</span>' :
                        '<span class="badge bg-danger-light text-danger">Inactive</span>';

                    // Update the row data
                    var rowData = [
                        $(rowToUpdate).find('td:eq(0)').html(), // Keep checkbox
                        '<div class="avatar avatar-sm rounded-circle"><span class="avatar-text bg-primary-light text-primary">' +
                        location.name.charAt(0) + '</span></div>',
                        location.name,
                        location.email || 'N/A',
                        '<span class="badge bg-primary-light text-primary">Location</span>',
                        '<div class="d-flex align-items-center gap-1"><span>' + (location.city || 'N/A') +
                        ', ' + (location.country || 'N/A') + '</span></div>',
                        '<div class="d-flex align-items-center gap-1"><span>' + (location.state || 'N/A') +
                        '</span></div>',
                        '<div class="d-flex align-items-center gap-1"><span>' + (location.address || 'N/A') +
                        '</span></div>',
                        statusBadge,
                        $(rowToUpdate).find('td:eq(9)').html() // Keep action buttons
                    ];

                    locationsTable.row(rowToUpdate).data(rowData).draw(false);

                    // Highlight the updated row
                    $(rowToUpdate).addClass('highlight-row');
                    setTimeout(function() {
                        $(rowToUpdate).removeClass('highlight-row');
                    }, 3000);
                }
            }

            // Function to show notification
            function showNotification(message, type) {
                var alertClass = 'alert-success';
                if (type === 'error') {
                    alertClass = 'alert-danger';
                }

                var notification = $('<div class="alert ' + alertClass +
                    ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>');

                // Append to the top of the content area
                $('.content').prepend(notification);

                // Auto dismiss after 3 seconds
                setTimeout(function() {
                    notification.alert('close');
                }, 3000);
            }

            // Handle view location details
            $(document).on('click', '[data-bs-target="#location_detail"]', function() {
                var locationId = $(this).data('location-id');

                // Fetch location details via AJAX
                $.ajax({
                    url: '/location/' + locationId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.location) {
                            var location = response.location;

                            // Populate the modal with location details
                            $('#detail-name').text(location.name);
                            $('#detail-email').text(location.email || 'N/A');
                            $('#detail-address').text(location.address || 'N/A');
                            $('#detail-city').text(location.city || 'N/A');
                            $('#detail-state').text(location.state || 'N/A');
                            $('#detail-country').text(location.country || 'N/A');
                            $('#detail-zip_code').text(location.zip_code || 'N/A');
                            $('#detail-status').text(location.status == 1 ? 'Active' :
                                'Inactive');
                            $('#detail-created_at').text(location.created_at);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching location details');
                    }
                });
            });

            // Handle edit location button
            $(document).on('click', '[data-bs-target="#offcanvas_edit"]', function(e) {
                // Prevent the default behavior (which would open the offcanvas immediately)
                e.preventDefault();

                var locationId = $(this).data('location-id');

                // Fetch location details via AJAX
                $.ajax({
                    url: '/location/' + locationId + '/edit',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.location) {
                            var location = response.location;

                            // Set the form action URL
                            $('#edit-location-form').attr('action', '/location/' + locationId);
                            $('#edit-location-form').data('location-id', locationId);

                            // Populate the form fields
                            $('#edit-name').val(location.name);
                            $('#edit-email').val(location.email);
                            $('#edit-address').val(location.address);
                            $('#edit-city').val(location.city);
                            $('#edit-state').val(location.state);
                            $('#edit-country').val(location.country);
                            $('#edit-zip_code').val(location.zip_code);
                            $('#edit-status').val(location.status);

                            // Manually open the offcanvas after data is loaded
                            var offcanvasElement = document.getElementById('offcanvas_edit');
                            var offcanvas = new bootstrap.Offcanvas(offcanvasElement);
                            offcanvas.show();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching location details for edit');
                        showNotification('Error loading location data. Please try again.', 'error');
                    }
                });
            });

            // Handle delete button
            $(document).on('click', '[data-bs-target="#delete_location"]', function() {
                var locationId = $(this).data('location-id');

                // Set the form action URL
                $('#delete-location-form').attr('action', '/location/' + locationId);
                $('#delete-location-form').data('location-id', locationId);
            });

            // Handle delete form submit
            $('#delete-location-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var locationId = form.data('location-id');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        // Close the modal
                        $('#delete_location').modal('hide');

                        // Remove the row from the table
                        var rows = locationsTable.rows().nodes();
                        $(rows).each(function() {
                            var actionCell = $(this).find('td:last-child');
                            var locationIdInRow = actionCell.find('a[data-location-id]')
                                .data('location-id');

                            if (locationIdInRow == locationId) {
                                locationsTable.row(this).remove().draw(false);
                                return false; // Break the loop
                            }
                        });

                        // Show a success message
                        showNotification('Location deleted successfully', 'success');
                    },
                    error: function(xhr) {
                        console.error('Error deleting location');
                        showNotification('Error deleting location. Please try again.', 'error');
                    }
                });
            });

            // We're now handling the edit functionality directly in the click event handler
            // No need for the show.bs.offcanvas event handler anymore

            // Handle delete button click
            $('#delete_location').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var locationId = button.data('location-id');
                var form = $('#delete-location-form');

                // Set the form action URL
                form.attr('action', '/location/' + locationId);
            });

            // Handle view button click
            $('#location_detail').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var locationId = button.data('location-id');

                // Fetch location data via AJAX
                $.ajax({
                    url: '/location/' + locationId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        var data = response.location || response;
                        $('#detail-name').text(data.name);
                        $('#detail-email').text(data.email || 'N/A');
                        $('#detail-address').text(data.address || 'N/A');
                        $('#detail-city').text(data.city || 'N/A');
                        $('#detail-state').text(data.state || 'N/A');
                        $('#detail-country').text(data.country || 'N/A');
                        $('#detail-zip_code').text(data.zip_code || 'N/A');
                        $('#detail-status').text(data.status == 1 ? 'Active' : 'Inactive');
                        $('#detail-created_at').text(data.created_at);
                    },
                    error: function(xhr) {
                        console.error('Error fetching location data');
                    }
                });
            });
        });
</script>
@endpush --}}
