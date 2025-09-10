<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'adminuser',
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
            'mobile' => '+91-9876543210',
            'employee_id' => 'EMP1001',
            'is_admin' => true,
            'is_user' => false,
            'is_login' => false,
            'status' => 1,
        ]);

        // Create client user
        // User::factory()->create([
        //     'first_name' => 'Client',
        //     'last_name' => 'User',
        //     'username' => 'clientuser',
        //     'name' => 'Client User',
        //     'email' => 'client@example.com',
        //     'password' => Hash::make('password'),
        //     'mobile' => '+91-9876543211',
        //     'employee_id' => 'EMP1002',
        //     'is_client' => true,
        //     'is_user' => true,
        //     'status' => 1,
        // ]);

        // Create regular users
        // User::factory(10)->create();

        // Location::create([
        //     'name' => 'Thaltej',
        //     'email' => 'thaltej@gmail.com',
        //     'address' => '123 Thaltej St',
        //     'city' => 'Thaltej',
        //     'state' => 'Thaltej',
        //     'country' => 'Thaltej',
        //     'zip_code' => '12345',
        //     'created_by' => 1,
        //     'updated_by' => 1,
        // ]);

        // Seed minimal single records for setup
        $this->call([
            SingleLocationSeeder::class,
            SingleCompanySeeder::class,
            SingleAreaSeeder::class,
            SingleRolePermissionSeeder::class,
            SingleDeviceSeeder::class,
            SingleScheduleSeeder::class,
            DeviceScheduleRelationSeeder::class,
        ]);
    }
}
