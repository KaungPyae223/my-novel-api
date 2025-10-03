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
        return [
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'username' => $this->faker->unique()->userName(),
            'password' => Hash::make('password123'), // default password
            'profile_image' => null,
            'cover_image' => null,
            'about' => $this->faker->sentence(12),
            'location' => $this->faker->city(),
            'phone' => $this->faker->phoneNumber(),
            'facebook' => 'https://facebook.com/' . Str::slug($this->faker->userName()),
            'twitter' => 'https://twitter.com/' . Str::slug($this->faker->userName()),
            'instagram' => 'https://instagram.com/' . Str::slug($this->faker->userName()),
            'youtube' => 'https://youtube.com/@' . Str::slug($this->faker->userName()),
            'profile_image_public_id' => null,
            'cover_image_public_id' => null,
            'save_history' => true,
            'email_verified_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
