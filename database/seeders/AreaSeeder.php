<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Location;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample areas with specific data
        $areas = [
            [
                'name' => 'North Business District',
                'description' => 'Primary business and commercial area in the northern part of the city with high foot traffic and premium office spaces.',
                'code' => 'NBD001',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'South Industrial Zone',
                'description' => 'Manufacturing and industrial facilities located in the southern region with excellent transportation connectivity.',
                'code' => 'SIZ002',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'East Residential Quarter',
                'description' => 'Family-oriented residential area with schools, parks, and community facilities in the eastern suburbs.',
                'code' => 'ERQ003',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'West Technology Hub',
                'description' => 'Innovation center and tech startup ecosystem with modern office buildings and research facilities.',
                'code' => 'WTH004',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Central Downtown',
                'description' => 'Historic city center with retail stores, restaurants, and cultural landmarks attracting tourists and locals.',
                'code' => 'CDT005',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Metropolitan Financial District',
                'description' => 'High-rise office buildings housing major banks, investment firms, and financial services companies.',
                'code' => 'MFD006',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Suburban Shopping Center',
                'description' => 'Modern retail complex with department stores, specialty shops, and entertainment venues for suburban families.',
                'code' => 'SSC007',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Urban Arts District',
                'description' => 'Creative neighborhood featuring galleries, theaters, music venues, and artist studios in a vibrant cultural setting.',
                'code' => 'UAD008',
                'status' => 1,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }

        // Create additional random areas using factory
        Area::factory(12)->create();

        // Create related data for all areas
        $this->createRelatedData();
    }

    /**
     * Create related data for areas (locations and companies)
     */
    private function createRelatedData(): void
    {
        $faker = Faker::create();

        Area::all()->each(function ($area) use ($faker) {
            // Create area locations
            $this->createAreaLocations($area, $faker);

            // Create area companies
            $this->createAreaCompanies($area, $faker);
        });
    }

    /**
     * Create locations for an area
     */
    private function createAreaLocations(Area $area, $faker): void
    {
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

            // Attach location to area
            $area->locations()->attach($location->id);
        }
    }

    /**
     * Create companies for an area
     */
    private function createAreaCompanies(Area $area, $faker): void
    {
        // Get existing companies or create new ones if needed
        $companies = Company::inRandomOrder()->limit($faker->numberBetween(2, 5))->get();

        if ($companies->isEmpty()) {
            // If no companies exist, create some
            $companies = Company::factory($faker->numberBetween(2, 5))->create();
        }

        // Attach companies to area
        $area->companies()->attach($companies->pluck('id')->toArray());
    }
}
