<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Schedule;
use App\Models\DeviceLayout;
use Illuminate\Database\Seeder;

class DeviceScheduleRelationSeeder extends Seeder
{
    /**
     * Ensure every device has at least one layout and one schedule.
     */
    public function run(): void
    {
        $devices = Device::all();

        foreach ($devices as $device) {
            $layout = DeviceLayout::firstOrCreate(
                [
                    'layout_name' => 'Default Full Screen',
                    'device_id' => $device->id,
                ],
                [
                    'layout_type' => DeviceLayout::LAYOUT_TYPE_FULL_SCREEN,
                    'status' => DeviceLayout::STATUS_ACTIVE,
                ]
            );

            Schedule::firstOrCreate(
                [
                    'schedule_name' => 'Default Schedule - ' . ($device->name ?? $device->unique_id)
                ],
                [
                    'device_id' => $device->id,
                    'layout_id' => $layout->id,
                ]
            );
        }
    }
}
