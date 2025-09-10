<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $areaTypes = [
            'North',
            'South',
            'East',
            'West',
            'Central',
            'Downtown',
            'Uptown',
            'Business District',
            'Residential',
            'Industrial',
            'Commercial',
            'Suburban',
            'Urban',
            'Rural',
            'Metropolitan',
            'District'
        ];

        return [
            'name' => fake()->randomElement($areaTypes) . ' ' . fake()->city(),
            'description' => fake()->sentence(10),
            'code' => strtoupper(fake()->lexify('???') . fake()->numerify('###')),
            'status' => fake()->randomElement([0, 1, 2, 3]), // 0: Delete, 1: Activate, 2: Inactive, 3: Block
            'created_by' => 1, // Default to admin user
            'updated_by' => 1, // Default to admin user
        ];
    }

    /**
     * Indicate that the area is activate.
     */
    public function activate(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Area::STATUS_ACTIVATE,
        ]);
    }

    /**
     * Indicate that the area is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Area::STATUS_INACTIVE,
        ]);
    }

    /**
     * Indicate that the area is blocked.
     */
    public function blocked(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Area::STATUS_BLOCK,
        ]);
    }

    /**
     * Indicate that the area is deleted.
     */
    public function deleted(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Area::STATUS_DELETE,
        ]);
    }
}
