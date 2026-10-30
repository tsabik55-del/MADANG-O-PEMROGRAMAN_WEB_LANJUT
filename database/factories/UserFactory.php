<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'wa_number' => '08' . fake()->numerify('##########'),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'pelanggan',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'owner',
        ]);
    }

    public function karyawan(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'karyawan',
        ]);
    }

    public function pelanggan(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'pelanggan',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
