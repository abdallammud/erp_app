<?php

namespace Database\Factories;

use App\Models\AllowanceType;
use App\Support\Hrm\PayComponentCalculationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AllowanceType>
 */
class AllowanceTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Housing', 'Transport', 'Hardship', 'Risk', 'Communication', 'Per Diem']);

        return [
            'name' => $name,
            'code' => strtoupper(str_replace(' ', '_', $name)).'-'.fake()->unique()->numerify('##'),
            'calculation_type' => PayComponentCalculationType::Fixed,
            'amount_or_rate' => fake()->numberBetween(20, 300),
            'taxable' => true,
            'is_active' => true,
        ];
    }
}
