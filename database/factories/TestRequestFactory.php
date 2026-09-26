<?php

namespace Database\Factories;

use App\Models\TestRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TestRequest>
 */
class TestRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'requester_id' => User::factory(),
            'title' => fake()->sentence(4),
            'reason' => fake()->optional()->paragraph(),
        ];
    }
}
