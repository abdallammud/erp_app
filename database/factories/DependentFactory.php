<?php

namespace Database\Factories;

use App\Models\Dependent;
use App\Models\Employee;
use App\Support\Hrm\DependentRelationship;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dependent>
 */
class DependentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'relationship' => fake()->randomElement(DependentRelationship::cases()),
            'date_of_birth' => fake()->dateTimeBetween('-80 years', '-1 year'),
            'is_emergency_contact' => false,
            'is_insurance_beneficiary' => false,
        ];
    }
}
