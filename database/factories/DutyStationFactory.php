<?php

namespace Database\Factories;

use App\Models\DutyStation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DutyStation>
 */
class DutyStationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city().' Office',
            'country_code' => fake()->countryCode(),
            'city' => fake()->city(),
            'is_active' => true,
        ];
    }
}
