<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'type' => Category::TYPE_ACHIEVEMENT,
            'slug' => Str::slug($name),
            'name' => Str::title($name),
        ];
    }

    public function gallery(): static
    {
        return $this->state(['type' => Category::TYPE_GALLERY]);
    }
}
