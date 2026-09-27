<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Support\Hrm\EmployeeStatus;
use App\Support\Hrm\Gender;
use App\Support\Hrm\StaffCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_number' => 'EMP-'.fake()->unique()->numerify('#####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(Gender::cases()),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'staff_category' => StaffCategory::Permanent,
            'status' => EmployeeStatus::Active,
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
        ];
    }
}
