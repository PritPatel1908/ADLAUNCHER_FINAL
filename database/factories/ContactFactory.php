<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
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
            'name' => fake()->name(),
            'email' => fake()->email(),
            'phone' => fake()->phoneNumber(),
            'designation' => fake()->jobTitle(),
            'is_primary' => false,
        ];
    }

    /**
     * Indicate that this is a primary contact.
     */
    public function primary(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => true,
        ]);
    }

    /**
     * Indicate that this is a secondary contact.
     */
    public function secondary(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_primary' => false,
        ]);
    }
}
