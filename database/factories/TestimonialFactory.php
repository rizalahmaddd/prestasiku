<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quote' => fake()->paragraph(),
            'name' => fake()->name(),
            'role' => 'Guru Pembina',
            'is_published' => true,
        ];
    }
}
