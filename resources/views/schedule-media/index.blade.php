@extends('layout.main')

@section('content')
    <div class="content pb-0">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Manage Medias</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('schedule.index') }}">Schedules</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Manage Medias</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-primary"><i
                            class="ti ti-arrow-left me-1"></i>Back</a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <p class="mb-2">Schedule ID: <strong>{{ $scheduleId ?? 'N/A' }}</strong></p>
                    <p class="text-muted mb-0">This page is a placeholder for managing schedule medias. JSON was showing
                        earlier; now this opens a proper page. We can build full CRUD here next.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
