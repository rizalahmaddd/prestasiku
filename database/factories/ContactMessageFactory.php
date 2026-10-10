<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'organization' => fake()->company(),
            'phone' => fake()->numerify('0812########'),
            'email' => fake()->safeEmail(),
            'topic' => 'undangan',
            'message' => fake()->paragraph(),
            'status' => ContactMessage::STATUS_NEW,
        ];
    }
}
