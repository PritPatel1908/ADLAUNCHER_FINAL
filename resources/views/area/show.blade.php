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
                    <h4 class="mb-1">Area Details</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('area.index') }}">Areas</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Area Details</li>
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
                        <a href="{{ route('area.index') }}"><i class="ti ti-arrow-narrow-left me-1"></i>Back to
                            Areas</a>
                    </div>

                    <!-- Area Header Card -->
                    <div class="card">
                        <div class="card-body pb-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-xxl avatar-rounded me-3 flex-shrink-0 bg-primary">
                                        <span class="avatar-text">{{ substr($area->name, 0, 1) }}</span>
                                        <span class="status {{ $area->status == 'active' ? 'online' : 'offline' }}"></span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">{{ $area->name }}</h5>
                                        <p class="mb-2">{{ $area->description ?: 'No description available' }}</p>
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <span
                                                class="badge {{ $area->status == 1 ? 'badge-soft-success' : ($area->status == 2 ? 'badge-soft-warning' : ($area->status == 3 ? 'badge-soft-danger' : 'badge-soft-secondary')) }} border-0 me-2">
                                                <i
                                                    class="ti {{ $area->status == 1 ? 'ti-check' : ($area->status == 2 ? 'ti-lock' : ($area->status == 3 ? 'ti-ban' : 'ti-trash')) }} me-1"></i>
                                                {{ $area->status == 1 ? 'Active' : ($area->status == 2 ? 'Inactive' : ($area->status == 3 ? 'Block' : 'Delete')) }}
                                            </span>
                                            @if ($area->code)
                                                <p class="d-inline-flex align-items-center mb-0 me-3">
                                                    <i class="ti ti-code text-warning me-1"></i> {{ $area->code }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    @if (\App\Helpers\PermissionHelper::canEdit('area'))
                                        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas"
                                            data-bs-target="#offcanvas_edit">
                                            <i class="ti ti-edit me-1"></i>Edit Area
                                        </a>
                                    @endif
                                    @if (\App\Helpers\PermissionHelper::canDelete('area'))
                                        <form action="{{ route('area.destroy', $area->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this area?')">
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

            <!-- Area Information Cards -->
            <div class="row">
                <!-- Area Overview -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Area Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Area Name</h6>
                                        <p>{{ $area->name }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Code</h6>
                                        <p>{{ $area->code ?: 'N/A' }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Description</h6>
                                        <p>{{ $area->description ?: 'No description available' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Status</h6>
                                        <p><span
                                                class="badge {{ $area->status == 1 ? 'badge-soft-success' : ($area->status == 2 ? 'badge-soft-warning' : ($area->status == 3 ? 'badge-soft-danger' : 'badge-soft-secondary')) }}">
                                                {{ $area->status == 1 ? 'Active' : ($area->status == 2 ? 'Inactive' : ($area->status == 3 ? 'Block' : 'Delete')) }}
                                            </span></p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Created By</h6>
                                        <p>{{ $area->createdByUser ? $area->createdByUser->name : 'N/A' }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Updated By</h6>
                                        <p>{{ $area->updatedByUser ? $area->updatedByUser->name : 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Area Statistics -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Area Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Locations</h6>
                                        <p class="h4 text-primary">{{ $area->locations->count() }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Companies</h6>
                                        <p class="h4 text-success">{{ $area->companies->count() }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Created</h6>
                                        <p class="h4 text-warning">{{ $area->created_at->format('d M Y') }}</p>
                                    </div>
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Last Updated</h6>
                                        <p class="h4 text-info">{{ $area->updated_at->format('d M Y') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6 class="fw-semibold">Created</h6>
                                <p class="text-muted">{{ $area->created_at->format('d M Y, h:i A') }}</p>
                                @if ($area->updated_at)
                                    <h6 class="fw-semibold">Last Updated</h6>
                                    <p class="text-muted">{{ $area->updated_at->format('d M Y, h:i A') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Area Details Sections -->
            <div class="row">
                <!-- Locations -->
                @if ($area->locations->count() > 0)
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Locations ({{ $area->locations->count() }})</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach ($area->locations as $location)
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3">
                                                <h6 class="fw-semibold">{{ $location->name }}</h6>
                                                <p class="mb-1"><i
                                                        class="ti ti-mail text-info me-2"></i>{{ $location->email ?: 'N/A' }}
                                                </p>
                                                <p class="mb-1"><i
                                                        class="ti ti-map-pin text-warning me-2"></i>{{ $location->address ?: 'N/A' }}
                                                </p>
                                                <p class="mb-0"><i
                                                        class="ti ti-building text-success me-2"></i>{{ $location->city ?: 'N/A' }},
                                                    {{ $location->state ?: 'N/A' }}, {{ $location->country ?: 'N/A' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Companies -->
                @if ($area->companies->count() > 0)
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Companies ({{ $area->companies->count() }})</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach ($area->companies as $company)
                                        <div class="col-md-6 mb-3">
                                            <div class="border rounded p-3">
                                                <h6 class="fw-semibold">{{ $company->name }}</h6>
                                                <p class="mb-1"><i
                                                        class="ti ti-building text-info me-2"></i>{{ $company->industry ?: 'N/A' }}
                                                </p>
                                                <p class="mb-1"><i
                                                        class="ti ti-mail text-warning me-2"></i>{{ $company->email ?: 'N/A' }}
                                                </p>
                                                <p class="mb-0"><i
                                                        class="ti ti-phone text-success me-2"></i>{{ $company->phone ?: 'N/A' }}
                                                </p>
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

    <!-- Edit Area Offcanvas -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_edit"
        aria-labelledby="offcanvas_edit_label">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvas_edit_label">Edit Area</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="card">
                <div class="card-body">
                    <form id="edit-area-form" method="POST" action="{{ route('area.update', $area->id) }}">
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
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Area Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="name"
                                                        id="edit-name" value="{{ $area->name }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Code</label>
                                                    <input type="text" class="form-control" name="code"
                                                        id="edit-code" value="{{ $area->code }}">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" name="description" id="edit-description" rows="3">{{ $area->description }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" name="status" id="edit-status">
                                                        <option value="1" {{ $area->status == 1 ? 'selected' : '' }}>
                                                            Activate</option>
                                                        <option value="2" {{ $area->status == 2 ? 'selected' : '' }}>
                                                            Inactive</option>
                                                        <option value="3" {{ $area->status == 3 ? 'selected' : '' }}>
                                                            Block</option>
                                                        <option value="0" {{ $area->status == 0 ? 'selected' : '' }}>
                                                            Delete</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Locations -->
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded collapsed"
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
                                                    <label class="form-label">Select Locations</label>
                                                    <select class="form-select select2-multiple" name="location_ids[]"
                                                        id="edit-location_ids" multiple>
                                                        @foreach (\App\Models\Location::all() as $location)
                                                            <option value="{{ $location->id }}"
                                                                {{ $area->locations->contains($location->id) ? 'selected' : '' }}>
                                                                {{ $location->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Companies -->
                            <div class="accordion-item border-top rounded mb-3">
                                <div class="accordion-header">
                                    <a href="#" class="accordion-button accordion-custom-button rounded collapsed"
                                        data-bs-toggle="collapse" data-bs-target="#company">
                                        <span class="avatar avatar-md rounded me-1"><i class="ti ti-building"></i></span>
                                        Company Info
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="company" data-bs-parent="#main_accordion">
                                    <div class="accordion-body border-top">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Select Companies</label>
                                                    <select class="form-select select2-multiple" name="company_ids[]"
                                                        id="edit-company_ids" multiple>
                                                        @foreach (\App\Models\Company::all() as $company)
                                                            <option value="{{ $company->id }}"
                                                                {{ $area->companies->contains($company->id) ? 'selected' : '' }}>
                                                                {{ $company->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alert for form submission -->
                        <div id="edit-form-alert" class="col-12" style="display: none;">
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                Area updated successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="button" data-bs-dismiss="offcanvas"
                                class="btn btn-sm btn-light me-2">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Update
                                Area</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Edit Area Offcanvas -->
@endsection

@push('js')
    <!-- Select2 CSS and JS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/datatable/area-show.js') }}" type="text/javascript"></script>

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

        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e9ecef !important;
            color: #495057 !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }
    </style>
@endpush
