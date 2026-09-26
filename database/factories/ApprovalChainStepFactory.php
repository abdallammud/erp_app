<?php

namespace Database\Factories;

use App\Models\ApprovalChain;
use App\Models\ApprovalChainStep;
use App\Support\Authorization\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApprovalChainStep>
 */
class ApprovalChainStepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'approval_chain_id' => ApprovalChain::factory(),
            'sequence' => 1,
            'approver_role' => Role::Supervisor->value,
            'label' => 'Supervisor review',
        ];
    }
}
