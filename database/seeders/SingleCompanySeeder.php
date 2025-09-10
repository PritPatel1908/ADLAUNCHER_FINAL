<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Location;
use App\Models\CompanyLocation;
use Illuminate\Database\Seeder;

class SingleCompanySeeder extends Seeder
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

        CompanyLocation::firstOrCreate(
            ['company_id' => $company->id, 'location_id' => $location->id],
            [
                'company_id' => $company->id,
                'location_id' => $location->id,
            ]
        );
    }
}
