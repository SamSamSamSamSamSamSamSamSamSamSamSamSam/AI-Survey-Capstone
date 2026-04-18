<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker; // Import Faker directly

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        // Create a local faker instance if the built-in one is failing
        $faker = Faker::create();

        return [
            'user_id_number' => $faker->unique()->numerify('20########'),
            'name' => $faker->name(),
            'email' => $faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'must_change_password' => false,
            'remember_token' => Str::random(10),
        ];
    }
}