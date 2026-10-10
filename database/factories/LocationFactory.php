<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'badge' => 'Lokasi',
            'color' => '#1d4ed8',
            'lat' => fake()->latitude(-8, -6),
            'lng' => fake()->longitude(106, 112),
            'zoom' => 15,
            'address' => fake()->address(),
        ];
    }
}
