<?php

namespace Database\Factories;

use App\Models\TaxBracket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaxBracket>
 */
class TaxBracketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'country_code' => 'SO',
            'sequence' => 1,
            'min_income' => 0,
            'max_income' => 1000,
            'rate' => 5,
        ];
    }
}
