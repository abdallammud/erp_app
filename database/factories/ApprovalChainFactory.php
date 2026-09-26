<?php

namespace Database\Factories;

use App\Models\ApprovalChain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApprovalChain>
 */
class ApprovalChainFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true).' chain',
            'action_type' => fake()->unique()->slug(2),
            'is_active' => true,
        ];
    }
}
