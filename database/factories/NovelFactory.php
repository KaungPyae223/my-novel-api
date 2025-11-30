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
            'status' => "published",
            'progress' => $this->faker->randomElement(config('base.progress')),
            'user_id' => $this->faker->numberBetween(1, 30), 
            'genre_id' => $this->faker->numberBetween(1, 10), 
        ];
    }
}
