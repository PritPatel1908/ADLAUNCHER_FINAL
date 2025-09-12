@extends('Layout.main')

@section('title', 'Dashboard')

@section('content')
    {{-- <div class="page-wrapper"> --}}
    <!-- Start Content -->
    <div class="content pb-0">

        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Devices Dashboard</h4>
            </div>
            <div class="gap-2 d-flex align-items-center flex-wrap">
                <div class="daterangepick form-control w-auto d-flex align-items-center">
                    <i class="ti ti-calendar text-dark me-2"></i>
                    <span class="reportrange-picker-field text-dark">23 May 2025 - 30 May 2025</span>
                </div>
                <a href="javascript:void(0);" class="btn btn-icon btn-outline-light shadow" data-bs-toggle="tooltip"
                    data-bs-placement="top" aria-label="Refresh" data-bs-original-title="Refresh"><i
                        class="ti ti-refresh"></i></a>
                <a href="javascript:void(0);" class="btn btn-icon btn-outline-light shadow" data-bs-toggle="tooltip"
                    data-bs-placement="top" aria-label="Collapse" data-bs-original-title="Collapse" id="collapse-header"><i
                        class="ti ti-transition-top"></i></a>
            </div>
        </div>
        <!-- End Page Header -->

        <!-- start row -->
        <div class="row">

            <div class="col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h6 class="mb-0">Recently Added Devices</h6>
                        <div class="dropdown">
                            <a class="dropdown-toggle btn btn-outline-light shadow" data-bs-toggle="dropdown"
                                href="javascript:void(0);">
                                Last 30 days
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="javascript:void(0);" class="dropdown-item">
                                    Last 15 days
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item">
                                    Last 30 days
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive custom-table">
                            <table class="table dataTable table-nowrap" id="deals-project">
                                <thead class="table-light">
                                    <tr>
                                        <th>Device Name</th>
                                        <th>Company</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentDevices ?? [] as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td>{{ $row['company'] ?? '-' }}</td>
                                            <td>{{ $row['location'] ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $statusClass = 'bg-pending';
                                                    $statusName = 'Open';
                                                    if (($row['status'] ?? null) === 1) {
                                                        $statusClass = 'bg-success';
                                                        $statusName = 'Active';
                                                    } elseif (($row['status'] ?? null) === 2) {
                                                        $statusClass = 'bg-warning';
                                                        $statusName = 'Inactive';
                                                    } elseif (($row['status'] ?? null) === 3) {
                                                        $statusClass = 'bg-danger';
                                                        $statusName = 'Blocked';
                                                    }
                                                @endphp
                                                <span
                                                    class="badge badge-pill {{ $statusClass }}">{{ $statusName }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div> <!-- end card body -->
                </div> <!-- end card -->
            </div> <!-- end col -->

            <div class="col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h6 class="mb-0">Devices by Status</h6>
                            <div class="d-flex align-items-center flex-wrap row-gap-3">
                                <div class="dropdown me-2">
                                    <a class="dropdown-toggle btn btn-outline-light shadow" data-bs-toggle="dropdown"
                                        href="javascript:void(0);">
                                        Sales Pipeline
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Marketing Pipeline
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Sales Pipeline
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Email
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Chats
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Operational
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a class="dropdown-toggle btn btn-outline-light shadow" data-bs-toggle="dropdown"
                                        href="javascript:void(0);">
                                        Last 30 Days
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Last 30 Days
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Last 15 Days
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Last 7 Days
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-0">
                        <div id="deals-chart"></div>
                    </div> <!-- end card body -->
                </div> <!-- end card -->
            </div> <!-- end col -->

        </div>
        <!-- end row -->

        <!-- start row -->
        <div class="row">

            <div class="col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h6 class="mb-0">Inactive Devices</h6>
                            <div class="d-flex align-items-center flex-wrap row-gap-3">
                                <div class="dropdown me-2">
                                    <a class="dropdown-toggle btn btn-outline-light shadow" data-bs-toggle="dropdown"
                                        href="javascript:void(0);">
                                        Marketing Pipeline
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Marketing Pipeline
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Sales Pipeline
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Email
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Chats
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Operational
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a class="dropdown-toggle btn btn-outline-light shadow" data-bs-toggle="dropdown"
                                        href="javascript:void(0);">
                                        Last 30 Days
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Last 30 Days
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Last 6 months
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Last 12 months
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-0">
                        <div id="last-chart"></div>
                    </div> <!-- end card body -->
                </div> <!-- end card -->
            </div> <!-- end col -->

            <div class="col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h6 class="mb-0">Active Devices</h6>
                            <div class="d-flex align-items-center flex-wrap row-gap-3">
                                <div class="dropdown me-2">
                                    <a class="dropdown-toggle btn btn-outline-light shadow" data-bs-toggle="dropdown"
                                        href="javascript:void(0);">
                                        Marketing Pipeline
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Marketing Pipeline
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Sales Pipeline
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Email
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Chats
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Operational
                                        </a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a class="dropdown-toggle btn btn-outline-light shadow" data-bs-toggle="dropdown"
                                        href="javascript:void(0);">
                                        Last 30 Days
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Last 30 Days
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Last 6 months
                                        </a>
                                        <a href="javascript:void(0);" class="dropdown-item">
                                            Last 12 months
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-0">
                        <div id="won-chart"></div>
                    </div> <!-- end card body -->
                </div> <!-- end card -->
            </div> <!-- end col -->

        </div>
        <!-- end row -->

        <!-- start row -->
        <div class="row">

            <div class="col-md-12 d-flex">
                <div class="card w-100">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h6 class="mb-0">Devices Trend</h6>
                        <div class="d-flex align-items-center flex-wrap row-gap-3">
                            <div class="dropdown me-2">
                                <a class="dropdown-toggle btn btn-outline-light shadow" data-bs-toggle="dropdown"
                                    href="javascript:void(0);">
                                    Sales Pipeline
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        Marketing Pipeline
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        Sales Pipeline
                                    </a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-toggle btn btn-outline-light shadow" data-bs-toggle="dropdown"
                                    href="javascript:void(0);">
                                    Last 30 Days
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        Last 3 months
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        Last 6 months
                                    </a>
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        Last 12 months
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-0">
                        <div id="deals-year"></div>
                    </div> <!-- end card body -->
                </div> <!-- end card -->
            </div> <!-- end col -->

        </div>
        <!-- end row -->

    </div>
    <!-- End Content -->
    {{-- </div> --}}
@endsection

@push('js')
    <script>
        // Inline charts using data from controller instead of dummy JS
        (function() {
            const devicesByStatus = @json($chart['devicesByStatus'] ?? ['labels' => [], 'series' => []]);

            if (document.querySelector('#deals-chart')) {
                const options = {
                    series: [{
                        name: 'Devices',
                        data: (devicesByStatus.series || [])
                    }],
                    chart: {
                        type: 'bar',
                        height: 280
                    },
                    xaxis: {
                        categories: (devicesByStatus.labels || [])
                    },
                    colors: ['#1abc9c']
                };
                const chart = new ApexCharts(document.querySelector('#deals-chart'), options);
                chart.render();
            }

            if (document.querySelector('#won-chart')) {
                const options = {
                    series: [{
                        data: (devicesByStatus.series || [])
                    }],
                    chart: {
                        type: 'bar',
                        height: 220
                    },
                    xaxis: {
                        categories: (devicesByStatus.labels || [])
                    },
                    colors: ['#28a745']
                };
                new ApexCharts(document.querySelector('#won-chart'), options).render();
            }

            if (document.querySelector('#last-chart')) {
                const options = {
                    series: [{
                        data: (devicesByStatus.series || [])
                    }],
                    chart: {
                        type: 'bar',
                        height: 220
                    },
                    xaxis: {
                        categories: (devicesByStatus.labels || [])
                    },
                    colors: ['#dc3545']
                };
                new ApexCharts(document.querySelector('#last-chart'), options).render();
            }

            if (document.querySelector('#deals-year')) {
                const options = {
                    series: [{
                        name: 'Devices',
                        data: (devicesByStatus.series || [])
                    }],
                    chart: {
                        type: 'line',
                        height: 260
                    },
                    xaxis: {
                        categories: (devicesByStatus.labels || [])
                    },
                    colors: ['#6f42c1']
                };
                new ApexCharts(document.querySelector('#deals-year'), options).render();
            }
        })();
    </script>
@endpush
