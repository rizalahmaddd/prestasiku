<?php

namespace Database\Factories;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Lomba '.fake()->unique()->words(3, true),
            'organizer' => fake()->company(),
            'description' => fake()->sentence(),
            'badge' => 'Juara 1',
            'icon' => 'medal',
            'tone' => 'amber',
            'medal' => 'emas',
            'level' => 'nasional',
            'achieved_on' => fake()->dateTimeBetween('-3 years')->format('Y-m-d'),
            'tags' => ['Tingkat Nasional'],
            'is_published' => true,
        ];
    }

    public function hidden(): static
    {
        return $this->state(['is_published' => false]);
    }
}
