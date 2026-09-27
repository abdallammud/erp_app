<?php

namespace Database\Factories;

use App\Models\DeductionType;
use App\Support\Hrm\DeductionCategory;
use App\Support\Hrm\PayComponentCalculationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeductionType>
 */
class DeductionTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Pension Contribution',
            'code' => 'DED-'.fake()->unique()->numerify('##'),
            'category' => DeductionCategory::Pension,
            'calculation_type' => PayComponentCalculationType::PercentageOfBase,
            'amount_or_rate' => 5,
            'is_active' => true,
        ];
    }
}
