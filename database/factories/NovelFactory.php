<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Novel>
 */
class NovelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'title' => $title,
            'unique_name' => Str::slug($title) . '-' . Str::random(6), 
            'description' => $this->faker->paragraph(5),
            'synopsis' => $this->faker->paragraph(3),
            'tags' => implode('/', $this->faker->words(5)),
            'image' => "https://images.unsplash.com/photo-1756806983687-203048d56220?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
            'image_public_id' => null,
            'status' => "published",
            'progress' => $this->faker->randomElement(config('base.progress')),
            'user_id' => $this->faker->numberBetween(1, 30), 
            'genre_id' => $this->faker->numberBetween(1, 10), 
        ];
    }
}
