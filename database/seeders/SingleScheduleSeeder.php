<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Schedule;
use App\Models\DeviceLayout;
use Illuminate\Database\Seeder;

class SingleScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $device = Device::first();
        if (!$device) {
            $this->call(SingleDeviceSeeder::class);
            $device = Device::first();
        }

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
            ['schedule_name' => 'Default Schedule - ' . ($device->name ?? $device->unique_id)],
            [
                'device_id' => $device->id,
                'layout_id' => $layout->id,
            ]
        );
    }
}
