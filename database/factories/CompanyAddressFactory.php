<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyAddress>
 */
class CompanyAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'type' => fake()->randomElement(['billing', 'shipping', 'office', 'warehouse']),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => fake()->country(),
            'zip_code' => fake()->postcode(),
        ];
    }

    /**
     * Indicate that this is a billing address.
     */
    public function billing(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'billing',
        ]);
    }

    /**
     * Indicate that this is a shipping address.
     */
    public function shipping(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'shipping',
        ]);
    }

    /**
     * Indicate that this is an office address.
     */
    public function office(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'office',
        ]);
    }
}
