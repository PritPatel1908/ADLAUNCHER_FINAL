<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class SingleLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::firstOrCreate(
            ['name' => 'HQ - Thaltej'],
            [
                'email' => 'hq@example.com',
                'address' => '123 Thaltej St',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'country' => 'India',
                'zip_code' => '380059',
                'status' => Location::STATUS_ACTIVE,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        );
    }
}
