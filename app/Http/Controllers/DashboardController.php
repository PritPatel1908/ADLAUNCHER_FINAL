<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Company;
use App\Models\Schedule;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display dashboard with real data for charts and tables.
     */
    public function index(Request $request)
    {
        // Recent items for the table (customize to your domain)
        $recentDevices = Device::with(['company'])
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(function (Device $device) {
                return [
                    'name' => $device->name,
                    'company' => optional($device->company)->name,
                    'status' => $device->status,
                ];
            });

        // Build chart dataset with friendly labels and zero-fill missing statuses
        $statusLabelByCode = [
            0 => 'Pending',
            1 => 'Active',
            2 => 'Inactive',
            3 => 'Blocked',
        ];

        $rawStatusCounts = Device::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $orderedLabels = array_values($statusLabelByCode);
        $orderedSeries = [];
        foreach ($statusLabelByCode as $code => $label) {
            $orderedSeries[] = (int) ($rawStatusCounts[$code] ?? 0);
        }

        $schedulesCount = Schedule::count();
        $companiesCount = Company::count();
        $devicesCount = Device::count();

        return view('Dashboard.index', [
            'recentDevices' => $recentDevices,
            'chart' => [
                'devicesByStatus' => [
                    'labels' => $orderedLabels,
                    'series' => $orderedSeries,
                ],
            ],
            'stats' => [
                'schedules' => $schedulesCount,
                'companies' => $companiesCount,
                'devices' => $devicesCount,
            ],
        ]);
    }
}
