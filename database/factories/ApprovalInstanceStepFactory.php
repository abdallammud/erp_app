<?php

namespace Database\Factories;

use App\Models\ApprovalInstance;
use App\Models\ApprovalInstanceStep;
use App\Support\Approvals\ApprovalStepStatus;
use App\Support\Authorization\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApprovalInstanceStep>
 */
class ApprovalInstanceStepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'approval_instance_id' => ApprovalInstance::factory(),
            'sequence' => 1,
            'approver_role' => Role::Supervisor->value,
            'label' => 'Supervisor review',
            'status' => ApprovalStepStatus::Pending,
        ];
    }
}
