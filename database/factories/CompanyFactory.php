<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyTypes = [
            'Technology',
            'Healthcare',
            'Finance',
            'Education',
            'Manufacturing',
            'Retail',
            'Real Estate',
            'Consulting',
            'Marketing',
            'Legal',
            'Construction',
            'Transportation',
            'Energy',
            'Media',
            'Food & Beverage'
        ];

        return [
            'name' => fake()->company(),
            'industry' => fake()->randomElement($companyTypes),
            'website' => fake()->url(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement([0, 1, 2, 3]), // 0: Inactive, 1: Active, 2: Blocked, 3: Deleted
            'created_by' => 1, // Default to admin user
            'updated_by' => 1, // Default to admin user
        ];
    }

    /**
     * Indicate that the company is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 1,
        ]);
    }

    /**
     * Indicate that the company is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 0,
        ]);
    }
}
