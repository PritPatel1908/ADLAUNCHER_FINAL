<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceLayout>
 */
class DeviceLayoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'layout_name' => $this->faker->words(2, true),
            'layout_type' => $this->faker->numberBetween(0, 3),
            'device_id' => \App\Models\Device::factory(),
            'status' => $this->faker->numberBetween(1, 3), // Exclude delete status (0) for default
        ];
    }
}
