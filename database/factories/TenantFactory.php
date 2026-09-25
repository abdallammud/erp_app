<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'countries' => [fake()->countryCode()],
            'default_currency' => 'USD',
            'timezone' => 'UTC',
            'fiscal_year_start_month' => 1,
            'is_active' => true,
        ];
    }
}
