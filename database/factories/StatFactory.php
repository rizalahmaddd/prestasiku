<?php

namespace Database\Factories;

use App\Models\Stat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stat>
 */
class StatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group' => Stat::GROUP_SUMMARY,
            'value' => fake()->numberBetween(1, 50),
            'label' => fake()->words(3, true),
            'tone' => 'slate',
        ];
    }
}
