<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'path' => 'media/'.fake()->unique()->lexify('????????').'.jpg',
            'mime' => 'image/jpeg',
            'size' => 120000,
            'width' => 1200,
            'height' => 900,
        ];
    }
}
