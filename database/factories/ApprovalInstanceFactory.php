<?php

namespace Database\Factories;

use App\Models\ApprovalChain;
use App\Models\ApprovalInstance;
use App\Models\TestRequest;
use App\Models\User;
use App\Support\Approvals\ApprovalStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApprovalInstance>
 *
 * Prefer App\Support\Approvals\ApprovalWorkflow::submit() over this
 * factory for anything testing real workflow behaviour — it also
 * creates the matching ApprovalInstanceStep rows, which this factory
 * alone does not.
 */
class ApprovalInstanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'approval_chain_id' => ApprovalChain::factory(),
            'requester_id' => User::factory(),
            'subject_type' => (new TestRequest)->getMorphClass(),
            'subject_id' => TestRequest::factory(),
            'status' => ApprovalStatus::Pending,
            'current_sequence' => 1,
        ];
    }
}
