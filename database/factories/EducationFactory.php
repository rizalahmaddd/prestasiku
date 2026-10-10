<?php

namespace Database\Factories;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Education>
 */
class EducationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'SMP '.fake()->city(),
            'period_label' => '2023 - Sekarang',
            'tone' => 'active',
            'is_published' => true,
        ];
    }
}
