<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chapter>
 */
class ChapterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'status' => "published", 
            'novel_id' => $this->faker->numberBetween(1, 50),
            'content' => $this->faker->paragraphs(10, true), 
            'summary' => $this->faker->paragraph(3),
            'share_count' => $this->faker->numberBetween(0, 500),
            'scheduled_date' => null,
        ];
    }
}
