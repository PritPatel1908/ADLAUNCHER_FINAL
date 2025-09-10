<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Company;
use App\Models\Location;
use Illuminate\Database\Seeder;

class SingleAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $location = Location::firstOrCreate(
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

        $company = Company::firstOrCreate(
            ['name' => 'Indian Infotech'],
            [
                'industry' => 'Technology',
                'website' => 'https://indianinfotech.org',
                'email' => 'support@indianinfotech.org',
                'phone' => '+91-9510862562',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        );

        $area = Area::firstOrCreate(
            ['code' => 'AREA001'],
            [
                'name' => 'Primary Area',
                'description' => 'Default area for initial setup',
                'status' => Area::STATUS_ACTIVATE,
                'created_by' => 1,
                'updated_by' => 1,
            ]
        );

        // Attach relations without duplicating existing pivot entries
        $area->locations()->syncWithoutDetaching([$location->id]);
        $area->companies()->syncWithoutDetaching([$company->id]);
    }
}
