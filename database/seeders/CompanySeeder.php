<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\CompanyNote;
use App\Models\CompanyAddress;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample companies with specific data
        $companies = [
            [
                'name' => 'TechCorp Solutions',
                'industry' => 'Technology',
                'website' => 'https://techcorp-solutions.com',
                'email' => 'info@techcorp-solutions.com',
                'phone' => '+1-555-0123',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'HealthFirst Medical',
                'industry' => 'Healthcare',
                'website' => 'https://healthfirst-medical.com',
                'email' => 'contact@healthfirst-medical.com',
                'phone' => '+1-555-0124',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Global Finance Group',
                'industry' => 'Finance',
                'website' => 'https://globalfinance-group.com',
                'email' => 'info@globalfinance-group.com',
                'phone' => '+1-555-0125',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'EduTech Innovations',
                'industry' => 'Education',
                'website' => 'https://edutech-innovations.com',
                'email' => 'hello@edutech-innovations.com',
                'phone' => '+1-555-0126',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Manufacturing Plus',
                'industry' => 'Manufacturing',
                'website' => 'https://manufacturing-plus.com',
                'email' => 'sales@manufacturing-plus.com',
                'phone' => '+1-555-0127',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }

        // Create additional random companies using factory
        Company::factory(15)->create();

        // Create related data for all companies
        $this->createRelatedData();
    }

    /**
     * Create related data for companies (addresses, contacts, notes, locations)
     */
    private function createRelatedData(): void
    {
        $faker = Faker::create();

        Company::all()->each(function ($company) use ($faker) {
            // Create company addresses
            $this->createCompanyAddresses($company, $faker);

            // Create company contacts
            $this->createCompanyContacts($company, $faker);

            // Create company notes
            $this->createCompanyNotes($company, $faker);

            // Create company locations
            $this->createCompanyLocations($company, $faker);
        });
    }

    /**
     * Create addresses for a company
     */
    private function createCompanyAddresses(Company $company, $faker): void
    {
        $addressTypes = ['billing', 'shipping', 'office'];

        foreach ($addressTypes as $type) {
            CompanyAddress::create([
                'company_id' => $company->id,
                'type' => $type,
                'address' => $faker->streetAddress(),
                'city' => $faker->city(),
                'state' => $faker->state(),
                'country' => $faker->country(),
                'zip_code' => $faker->postcode(),
            ]);
        }
    }

    /**
     * Create contacts for a company
     */
    private function createCompanyContacts(Company $company, $faker): void
    {
        $contactCount = $faker->numberBetween(1, 3);

        for ($i = 0; $i < $contactCount; $i++) {
            Contact::create([
                'company_id' => $company->id,
                'name' => $faker->name(),
                'email' => $faker->email(),
                'phone' => $faker->phoneNumber(),
                'designation' => $faker->jobTitle(),
                'is_primary' => $i === 0, // First contact is primary
            ]);
        }
    }

    /**
     * Create notes for a company
     */
    private function createCompanyNotes(Company $company, $faker): void
    {
        $noteCount = $faker->numberBetween(1, 2);

        for ($i = 0; $i < $noteCount; $i++) {
            CompanyNote::create([
                'company_id' => $company->id,
                'note' => $faker->paragraph(3),
                'created_by' => 1,
                'status' => 1,
            ]);
        }
    }

    /**
     * Create locations for a company
     */
    private function createCompanyLocations(Company $company, $faker): void
    {
        // Get existing locations or create new ones if needed
        $locations = Location::inRandomOrder()->limit($faker->numberBetween(1, 3))->get();

        if ($locations->isEmpty()) {
            // If no locations exist, create some
            $locationCount = $faker->numberBetween(1, 3);
            for ($i = 0; $i < $locationCount; $i++) {
                $location = Location::create([
                    'name' => $faker->city() . ' ' . $faker->randomElement(['Center', 'Hub', 'Zone', 'District']),
                    'email' => $faker->email(),
                    'address' => $faker->streetAddress(),
                    'city' => $faker->city(),
                    'state' => $faker->state(),
                    'country' => $faker->country(),
                    'zip_code' => $faker->postcode(),
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
                $locations->push($location);
            }
        }

        // Attach locations to company
        $company->locations()->attach($locations->pluck('id')->toArray());
    }
}
