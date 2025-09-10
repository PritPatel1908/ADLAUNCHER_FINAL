<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Area;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

        /**
     * Test that companies are seeded correctly.
     */
    public function test_companies_are_seeded(): void
    {
        $this->seed(\Database\Seeders\CompanySeeder::class);

        // Check that companies were created
        $this->assertGreaterThan(0, Company::count());
        
        // Check that specific companies exist
        $this->assertDatabaseHas('companies', [
            'name' => 'TechCorp Solutions',
            'industry' => 'Technology',
        ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'HealthFirst Medical',
            'industry' => 'Healthcare',
        ]);

        // Check that related data was created
        $company = Company::first();
        $this->assertGreaterThan(0, $company->addresses()->count());
        $this->assertGreaterThan(0, $company->contacts()->count());
        $this->assertGreaterThan(0, $company->notes()->count());
    }

        /**
     * Test that areas are seeded correctly.
     */
    public function test_areas_are_seeded(): void
    {
        $this->seed(\Database\Seeders\AreaSeeder::class);

        // Check that areas were created
        $this->assertGreaterThan(0, Area::count());
        
        // Check that specific areas exist
        $this->assertDatabaseHas('areas', [
            'name' => 'North Business District',
            'code' => 'NBD001',
        ]);

        $this->assertDatabaseHas('areas', [
            'name' => 'West Technology Hub',
            'code' => 'WTH004',
        ]);

        // Check that related data was created
        $area = Area::first();
        $this->assertGreaterThan(0, $area->locations()->count());
    }

    /**
     * Test that the main database seeder includes company and area seeders.
     */
    public function test_database_seeder_includes_company_and_area_seeders(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        // Check that both companies and areas were created
        $this->assertGreaterThan(0, Company::count());
        $this->assertGreaterThan(0, Area::count());
    }
}
