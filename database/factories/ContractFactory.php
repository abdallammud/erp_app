<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\SalaryGrade;
use App\Support\Hrm\ContractStatus;
use App\Support\Hrm\ContractType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'salary_grade_id' => SalaryGrade::factory(),
            'type' => ContractType::FixedTerm,
            'status' => ContractStatus::Active,
            'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'end_date' => fake()->dateTimeBetween('+6 months', '+2 years'),
        ];
    }
}
