<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->jobTitle(),
            'grade' => fake()->randomElement(['G4', 'G5', 'P1', 'P2', 'P3', 'P4']),
            'is_active' => true,
        ];
    }
}
