<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $middleName = fake()->boolean(30) ? fake()->firstName() : null;
        $username = strtolower($firstName . $lastName . fake()->numberBetween(1, 999));

        return [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'username' => $username,
            'name' => $firstName . ' ' . $lastName,
            'gender' => fake()->randomElement([1, 2]), // 1 for male, 2 for female
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->phoneNumber(),
            'employee_id' => 'EMP' . fake()->unique()->numberBetween(1000, 9999),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'date_of_joining' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_admin' => fake()->boolean(10), // 10% chance of being admin
            'is_client' => fake()->boolean(30), // 30% chance of being client
            'is_user' => fake()->boolean(60), // 60% chance of being user
            'is_login' => fake()->boolean(80), // 80% chance of being logged in
            'last_ip_address' => fake()->ipv4(),
            'last_url' => fake()->url(),
            'last_login_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'status' => fake()->randomElement([1, 2]), // 1 for active, 2 for inactive
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
