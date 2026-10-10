<?php

namespace Database\Factories;

use App\Models\RoadmapStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoadmapStep>
 */
class RoadmapStepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'period' => '2023 – 2024',
            'title' => fake()->sentence(3),
            'state' => 'normal',
        ];
    }
}
