<?php

namespace Database\Factories;

use App\Models\Interest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Interest>
 */
class InterestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'icon' => 'star',
            'tone' => 'royal',
            'tags' => ['Contoh'],
        ];
    }
}
