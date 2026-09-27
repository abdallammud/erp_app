<?php

namespace Database\Factories;

use App\Models\SalaryGrade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalaryGrade>
 */
class SalaryGradeFactory extends Factory
{
    public function definition(): array
    {
        $min = fake()->numberBetween(500, 3000) * 10;

        return [
            'name' => 'Grade '.fake()->randomElement(['G1', 'G2', 'G3', 'G4', 'G5', 'P1', 'P2', 'P3', 'P4', 'P5']),
            'code' => fake()->unique()->bothify('SG-###'),
            'min_salary' => $min,
            'max_salary' => $min * 2,
            'currency' => 'USD',
            'is_active' => true,
        ];
    }
}
