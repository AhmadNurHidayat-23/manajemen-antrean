<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QueueTicketFactory extends Factory
{
    public function definition(): array
    {
        return ['service_date' => now()->toDateString(), 'sequence' => fake()->unique()->numberBetween(1, 9999),
            'status' => 'waiting', 'called_at' => null, 'finished_at' => null];
    }
}
