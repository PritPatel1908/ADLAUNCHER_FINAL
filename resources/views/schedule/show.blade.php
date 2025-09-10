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
                    <h4 class="mb-1">Schedule Details</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('schedule.index') }}">Schedules</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Schedule Details</li>
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

            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <a href="{{ route('schedule.index') }}"><i class="ti ti-arrow-narrow-left me-1"></i>Back to
                            Schedules</a>
                    </div>
                    <div class="card">
                        <div class="card-body pb-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar avatar-xxl avatar-rounded me-3 flex-shrink-0 bg-primary">
                                        <i class="ti ti-calendar"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">{{ $schedule->schedule_name }}</h5>
                                        <p class="mb-2">
                                            {{ optional($schedule->device)->name ?? optional($schedule->device)->unique_id }}
                                        </p>
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <!-- Start/End badges removed as per requirement -->
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    @if (\App\Helpers\PermissionHelper::canEdit('schedule'))
                                        <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas"
                                            data-bs-target="#offcanvas_edit"><i class="ti ti-edit me-1"></i>Edit
                                            Schedule</a>
                                    @endif
                                    @if (\App\Helpers\PermissionHelper::canDelete('schedule'))
                                        <form action="{{ route('schedule.destroy', $schedule->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this schedule?')">
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

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Schedule Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Name</h6>
                                        <p>{{ $schedule->schedule_name }}</p>
                                    </div>
                                    <!-- Start At removed from overview -->
                                </div>
                                <div class="col-md-6">
                                    <!-- End At removed from overview -->
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Created</h6>
                                        <p class="text-muted">{{ $schedule->created_at->format('d M Y, h:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Target</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Device</h6>
                                        <p>{{ optional($schedule->device)->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6 class="fw-semibold">Layout</h6>
                                        <p>{{ optional($schedule->layout)->layout_name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-0">Schedule Medias</h5>
                            </div>
                            {{-- <a class="btn btn-primary btn-sm"
                                href="{{ route('schedule-media.index') }}?schedule_id={{ $schedule->id }}"><i
                                    class="ti ti-plus me-1"></i>Manage Medias</a> --}}
                        </div>
                        <div class="card-body">
                            @if ($schedule->medias && $schedule->medias->count())
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Type</th>
                                                <th>Screen</th>
                                                <th>Start At</th>
                                                <th>End At</th>
                                                <th>Preview</th>
                                                <th>Created At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($schedule->medias as $m)
                                                <tr>
                                                    <td>{{ $m->title }}</td>
                                                    <td>{{ ucfirst($m->media_type) }}</td>
                                                    <td>
                                                        @php($screen = \App\Models\DeviceScreen::find($m->screen_id))
                                                        {{ $screen ? 'Screen ' . $screen->screen_no : 'N/A' }}
                                                    </td>
                                                    <td>{{ $m->schedule_start_date_time ? \Carbon\Carbon::parse($m->schedule_start_date_time)->format('d M Y, h:i A') : 'N/A' }}
                                                    </td>
                                                    <td>
                                                        @if ($m->play_forever)
                                                            Play Forever
                                                        @else
                                                            {{ $m->schedule_end_date_time ? \Carbon\Carbon::parse($m->schedule_end_date_time)->format('d M Y, h:i A') : 'N/A' }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary preview-media-btn"
                                                            data-media-file="{{ $m->media_file }}"
                                                            data-media-type="{{ $m->media_type }}"
                                                            data-media-title="{{ $m->title }}">
                                                            <i class="ti ti-eye"></i> Preview
                                                        </button>
                                                    </td>
                                                    <td>{{ optional($m->created_at)->format('d M Y, h:i A') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="ti ti-photo text-muted" style="font-size: 3rem;"></i>
                                    <h6 class="text-muted mt-2">No media found</h6>
                                    <p class="text-muted">Add media items to this schedule.</p>
                                    <a class="btn btn-primary btn-sm"
                                        href="{{ route('schedule-media.index') }}?schedule_id={{ $schedule->id }}"><i
                                            class="ti ti-plus me-1"></i>Add Media</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    <form id="edit-schedule-form" method="POST" action="{{ route('schedule.update', $schedule->id) }}">
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
                                                        id="edit-schedule_name" value="{{ $schedule->schedule_name }}"
                                                        required>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Device</label>
                                                    <select class="form-control select2" name="device_id"
                                                        id="edit-device_id" data-toggle="select2">
                                                        <option value="">Select device...</option>
                                                        @foreach (\App\Models\Device::where('status', 1)->get() as $device)
                                                            <option value="{{ $device->id }}"
                                                                {{ $schedule->device_id == $device->id ? 'selected' : '' }}>
                                                                {{ $device->name }} - {{ $device->unique_id }}</option>
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
                                                        @if ($schedule->device_id)
                                                            @foreach (\App\Models\DeviceLayout::where('device_id', $schedule->device_id)->where('status', 1)->get() as $layout)
                                                                <option value="{{ $layout->id }}"
                                                                    {{ $schedule->layout_id == $layout->id ? 'selected' : '' }}>
                                                                    {{ $layout->layout_name }}</option>
                                                            @endforeach
                                                        @endif
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
                                        <span class="badge badge-soft-info ms-2">Multiple Media Support</span>
                                    </a>
                                </div>
                                <div class="accordion-collapse collapse" id="edit_media"
                                    data-bs-parent="#edit_main_accordion">
                                    <div class="accordion-body border-top">
                                        <div id="edit-media-container">
                                            @if ($schedule->medias && $schedule->medias->count())
                                                @foreach ($schedule->medias as $index => $media)
                                                    <div class="media-item border rounded p-3 mb-3"
                                                        data-media-id="{{ $media->id }}">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Media Title</label>
                                                                    <input type="text" class="form-control"
                                                                        name="edit_media_title[]"
                                                                        value="{{ $media->title }}"
                                                                        placeholder="Enter media title">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Media Type</label>
                                                                    <select class="form-control select2"
                                                                        name="edit_media_type[]" data-toggle="select2">
                                                                        <option value="">Select type...</option>
                                                                        <option value="image"
                                                                            {{ $media->media_type == 'image' ? 'selected' : '' }}>
                                                                            Image</option>
                                                                        <option value="video"
                                                                            {{ $media->media_type == 'video' ? 'selected' : '' }}>
                                                                            Video</option>
                                                                        <option value="audio"
                                                                            {{ $media->media_type == 'audio' ? 'selected' : '' }}>
                                                                            Audio</option>
                                                                        <option value="mp4"
                                                                            {{ $media->media_type == 'mp4' ? 'selected' : '' }}>
                                                                            MP4</option>
                                                                        <option value="png"
                                                                            {{ $media->media_type == 'png' ? 'selected' : '' }}>
                                                                            PNG</option>
                                                                        <option value="jpg"
                                                                            {{ $media->media_type == 'jpg' ? 'selected' : '' }}>
                                                                            JPG</option>
                                                                        <option value="pdf"
                                                                            {{ $media->media_type == 'pdf' ? 'selected' : '' }}>
                                                                            PDF</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Screen</label>
                                                                    <select class="form-control select2"
                                                                        name="edit_media_screen_id[]"
                                                                        data-toggle="select2">
                                                                        <option value="">Select screen...</option>
                                                                        @if ($schedule->device_id)
                                                                            @foreach (\App\Models\DeviceScreen::where('device_id', $schedule->device_id)->get() as $screen)
                                                                                <option value="{{ $screen->id }}"
                                                                                    {{ $media->screen_id == $screen->id ? 'selected' : '' }}>
                                                                                    Screen {{ $screen->screen_no }}
                                                                                </option>
                                                                            @endforeach
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <div class="mb-3">
                                                                    <label class="form-label">&nbsp;</label>
                                                                    <button type="button"
                                                                        class="btn btn-danger btn-sm w-100 remove-media"
                                                                        data-media-id="{{ $media->id }}">
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
                                                                        class="form-control edit-media-duration-input"
                                                                        name="edit_media_duration_seconds[]"
                                                                        min="1" max="86400"
                                                                        value="{{ $media->duration_seconds }}"
                                                                        placeholder="e.g. 15">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Start At</label>
                                                                    <input type="datetime-local" class="form-control"
                                                                        name="edit_media_start_date_time[]"
                                                                        value="{{ $media->schedule_start_date_time ? \Carbon\Carbon::parse($media->schedule_start_date_time)->format('Y-m-d\\TH:i') : '' }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label">End At</label>
                                                                    <input type="datetime-local" class="form-control"
                                                                        name="edit_media_end_date_time[]"
                                                                        value="{{ $media->schedule_end_date_time ? \Carbon\Carbon::parse($media->schedule_end_date_time)->format('Y-m-d\\TH:i') : '' }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3 d-flex align-items-center">
                                                                <div class="form-check mt-4">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="edit_media_play_forever[]" value="1"
                                                                        {{ $media->play_forever ? 'checked' : '' }}>
                                                                    <label class="form-check-label">Play Forever</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Current Media File</label>
                                                                    <div class="form-control-plaintext">
                                                                        <small
                                                                            class="text-muted">{{ $media->media_file }}</small>
                                                                    </div>
                                                                    <input type="file"
                                                                        class="form-control edit-media-file-input mt-2"
                                                                        name="edit_media_file[]"
                                                                        accept="image/*,video/*,audio/*,.mp4,.png,.jpg,.pdf">
                                                                    <input type="hidden" name="edit_media_id[]"
                                                                        value="{{ $media->id }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="media-item border rounded p-3 mb-3">
                                                    <input type="hidden" name="edit_media_id[]" value="">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label class="form-label">Media Title</label>
                                                                <input type="text" class="form-control"
                                                                    name="edit_media_title[]"
                                                                    placeholder="Enter media title">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="mb-3">
                                                                <label class="form-label">Media Type</label>
                                                                <select class="form-control select2"
                                                                    name="edit_media_type[]" data-toggle="select2">
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
                                                                    @if ($schedule->device_id)
                                                                        @foreach (\App\Models\DeviceScreen::where('device_id', $schedule->device_id)->get() as $screen)
                                                                            <option value="{{ $screen->id }}">
                                                                                Screen {{ $screen->screen_no }}</option>
                                                                        @endforeach
                                                                    @endif
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
                                                                    class="form-control edit-media-duration-input"
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
                                                                <input type="file"
                                                                    class="form-control edit-media-file-input"
                                                                    name="edit_media_file[]"
                                                                    accept="image/*,video/*,audio/*,.mp4,.png,.jpg,.pdf">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
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

    <!-- Media Preview Modal -->
    <div class="modal fade" id="mediaPreviewModal" tabindex="-1" aria-labelledby="mediaPreviewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mediaPreviewModalLabel">Media Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="mediaPreviewContent">
                        <!-- Media content will be loaded here -->
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
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/chunked-upload.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/upload-queue.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/datatable/schedule-show.js') }}" type="text/javascript"></script>
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
        #edit-upload-progress-container {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 10px !important;
            padding: 20px !important;
            margin: 15px 0 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
        }

        #edit-upload-progress-container h3 {
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

        /* Media Preview Styles */
        .media-preview-container {
            max-width: 100%;
            max-height: 500px;
            overflow: hidden;
        }

        .media-preview-container img,
        .media-preview-container video {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
        }

        .media-preview-container audio {
            width: 100%;
        }

        .media-preview-container iframe {
            width: 100%;
            height: 400px;
            border: none;
        }

        .media-preview-placeholder {
            padding: 2rem;
            color: #6c757d;
        }

        /* Progress Bar Styles */
        #edit-upload-progress-container {
            margin-top: 1rem;
        }

        #edit-upload-progress-container .card {
            border: 1px solid #e3e6f0;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

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

        #edit-upload-progress-text {
            color: white;
            font-weight: 600;
            font-size: 0.75rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        #edit-upload-status-text {
            font-size: 0.75rem;
            color: #858796;
        }
    </style>

    <script>
        $(document).ready(function() {
            // Media preview functionality
            $('.preview-media-btn').on('click', function() {
                const mediaFile = $(this).data('media-file');
                const mediaType = $(this).data('media-type');
                const mediaTitle = $(this).data('media-title');

                // Update modal title
                $('#mediaPreviewModalLabel').text('Preview: ' + mediaTitle);

                // Clear previous content
                $('#mediaPreviewContent').empty();

                // Generate media URL
                const mediaUrl = '{{ asset('storage/') }}/' + mediaFile;

                let mediaHtml = '';

                // Create appropriate media element based on type
                if (mediaType === 'image' || mediaType === 'png' || mediaType === 'jpg' || mediaType ===
                    'jpeg') {
                    mediaHtml = `
                        <div class="media-preview-container">
                            <img src="${mediaUrl}" alt="${mediaTitle}" class="img-fluid rounded">
                        </div>
                    `;
                } else if (mediaType === 'video' || mediaType === 'mp4') {
                    mediaHtml = `
                        <div class="media-preview-container">
                            <video controls class="w-100">
                                <source src="${mediaUrl}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    `;
                } else if (mediaType === 'audio') {
                    mediaHtml = `
                        <div class="media-preview-container">
                            <audio controls class="w-100">
                                <source src="${mediaUrl}" type="audio/mpeg">
                                Your browser does not support the audio tag.
                            </audio>
                        </div>
                    `;
                } else if (mediaType === 'pdf') {
                    mediaHtml = `
                        <div class="media-preview-container">
                            <iframe src="${mediaUrl}" type="application/pdf">
                                <p>Your browser does not support PDFs. <a href="${mediaUrl}" target="_blank">Click here to download the PDF</a></p>
                            </iframe>
                        </div>
                    `;
                } else {
                    mediaHtml = `
                        <div class="media-preview-placeholder">
                            <i class="ti ti-file" style="font-size: 3rem;"></i>
                            <h6 class="mt-2">Preview not available</h6>
                            <p>This file type cannot be previewed.</p>
                            <a href="${mediaUrl}" target="_blank" class="btn btn-primary">
                                <i class="ti ti-download"></i> Download File
                            </a>
                        </div>
                    `;
                }

                $('#mediaPreviewContent').html(mediaHtml);

                // Show modal
                $('#mediaPreviewModal').modal('show');
            });
        });
    </script>
@endpush
