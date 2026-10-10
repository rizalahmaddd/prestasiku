<?php

namespace Database\Factories;

use App\Models\GalleryItem;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryItem>
 */
class GalleryItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'place' => fake()->city(),
            'taken_on' => fake()->dateTimeBetween('-2 years')->format('Y-m-d'),
            'image_id' => Media::factory(),
            'is_published' => true,
        ];
    }
}
