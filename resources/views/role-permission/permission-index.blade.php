@extends('Layout.main')

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
                    <h4 class="mb-1">Permissions</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Permissions</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
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
                    <a href="javascript:void(0);" class="btn btn-icon btn-outline-info shadow" data-bs-toggle="tooltip"
                        data-bs-placement="top" aria-label="Refresh" data-bs-original-title="Refresh"><i
                            class="ti ti-refresh"></i></a>
                    <a href="javascript:void(0);" class="btn btn-icon btn-outline-warning shadow" data-bs-toggle="tooltip"
                        data-bs-placement="top" aria-label="Collapse" data-bs-original-title="Collapse"
                        id="collapse-header"><i class="ti ti-transition-top"></i></a>
                </div>
            </div>
            <!-- End Page Header -->

            <!-- Role Selection Card -->
            <div class="card border-0 rounded-0 mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Select Role to Manage Permissions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-select" id="role-selector">
                                <option value="">Select a Role</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-primary" id="manage-permissions-btn" disabled>
                                <i class="ti ti-settings me-2"></i>Manage Permissions
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permission Management Card -->
            <div class="card border-0 rounded-0" id="permission-card" style="display: none;">
                <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <h6 class="mb-0">Role Name : <span class="text-danger" id="selected-role-name">-</span></h6>
                    <div class="d-flex gap-2">
                        <div class="form-check mb-1">
                            <input type="checkbox" class="form-check-input" id="select-all-modules">
                            <label class="form-check-label" for="select-all-modules">Allow All Modules</label>
                        </div>
                        <button class="btn btn-success btn-sm" id="save-permissions">
                            <i class="ti ti-device-floppy me-1"></i>Save Permissions
                        </button>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Permission List -->
                    <div class="table-responsive custom-table">
                        <table class="table table-nowrap" id="permission_list">
                            <thead class="table-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Modules</th>
                                    <th>Sub Modules</th>
                                    <th>View</th>
                                    <th>Create</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                    <th>Import</th>
                                    <th>Export</th>
                                    <th>Manage Columns</th>
                                    <th>Allow All</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="datatable-length"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="datatable-paginate"></div>
                        </div>
                    </div>
                    <!-- /Permission List -->

                </div>
            </div>
            <!-- card end -->
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/datatable/permission-list.js') }}" type="text/javascript"></script>
@endpush
