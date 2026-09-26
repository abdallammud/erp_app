<?php

namespace App\Support\Approvals;

use App\Events\Approvals\ApprovalInstanceFinished;
use App\Events\Approvals\ApprovalStepActedOn;
use App\Models\ApprovalChain;
use App\Models\ApprovalInstance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The single entry point every module uses to submit something for
 * approval and to act on it — "Status API/component reused by every
 * future approval UI (leave, payroll, requisition, etc.)" per
 * docs/build/00-build-plan.md Step 0.6. Don't build approval logic
 * directly against the Approval* models from elsewhere; go through this.
 *
 * Segregation of duties (docs/03-roles-and-permissions.md) is enforced
 * here, structurally: canAct() always denies the requester, even if
 * they hold the step's role.
 */
class ApprovalWorkflow
{
    public function submit(ApprovalChain $chain, Model $subject, User $requester): ApprovalInstance
    {
        $steps = $chain->steps;

        if ($steps->isEmpty()) {
            throw new RuntimeException("Approval chain [{$chain->name}] has no steps configured.");
        }

        return DB::transaction(function () use ($chain, $subject, $requester, $steps) {
            $instance = ApprovalInstance::create([
                'approval_chain_id' => $chain->id,
                'requester_id' => $requester->id,
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'status' => ApprovalStatus::Pending,
                'current_sequence' => $steps->first()->sequence,
            ]);

            foreach ($steps as $chainStep) {
                $instance->steps()->create([
                    'sequence' => $chainStep->sequence,
                    'approver_role' => $chainStep->approver_role,
                    'label' => $chainStep->label,
                    'status' => ApprovalStepStatus::Pending,
                ]);
            }

            return $instance->load('steps');
        });
    }

    /**
     * Whether $actor can act (approve/reject) on $instance's current
     * step right now.
     */
    public function canAct(ApprovalInstance $instance, User $actor): bool
    {
        if ($instance->status !== ApprovalStatus::Pending) {
            return false;
        }

        $step = $instance->currentStep();

        if (! $step) {
            return false;
        }

        // Segregation of duties: the requester can never approve their
        // own request, even if they happen to hold the step's role —
        // see docs/03-roles-and-permissions.md.
        if ($actor->id === $instance->requester_id) {
            return false;
        }

        return $actor->hasRole($step->approver_role);
    }

    public function approve(ApprovalInstance $instance, User $actor, ?string $comment = null): ApprovalInstance
    {
        if (! $this->canAct($instance, $actor)) {
            throw new RuntimeException('This user cannot approve this request at its current step.');
        }

        return DB::transaction(function () use ($instance, $actor, $comment) {
            $step = $instance->currentStep();

            $step->update([
                'status' => ApprovalStepStatus::Approved,
                'acted_by' => $actor->id,
                'acted_at' => now(),
                'comment' => $comment,
            ]);

            $nextStep = $instance->steps()->where('sequence', '>', $step->sequence)->first();

            if ($nextStep) {
                $instance->update(['current_sequence' => $nextStep->sequence]);
            } else {
                $instance->update(['status' => ApprovalStatus::Approved, 'current_sequence' => null]);
            }

            $instance = $instance->fresh(['steps']);

            ApprovalStepActedOn::dispatch($instance, $step->fresh());

            if ($instance->status === ApprovalStatus::Approved) {
                ApprovalInstanceFinished::dispatch($instance);
            }

            return $instance;
        });
    }

    public function reject(ApprovalInstance $instance, User $actor, ?string $comment = null): ApprovalInstance
    {
        if (! $this->canAct($instance, $actor)) {
            throw new RuntimeException('This user cannot reject this request at its current step.');
        }

        return DB::transaction(function () use ($instance, $actor, $comment) {
            $step = $instance->currentStep();

            $step->update([
                'status' => ApprovalStepStatus::Rejected,
                'acted_by' => $actor->id,
                'acted_at' => now(),
                'comment' => $comment,
            ]);

            // Remaining steps never got a chance to act — mark them
            // Skipped rather than leaving them looking still-pending.
            $instance->steps()
                ->where('sequence', '>', $step->sequence)
                ->update(['status' => ApprovalStepStatus::Skipped]);

            $instance->update(['status' => ApprovalStatus::Rejected, 'current_sequence' => null]);

            $instance = $instance->fresh(['steps']);

            ApprovalStepActedOn::dispatch($instance, $step->fresh());
            ApprovalInstanceFinished::dispatch($instance);

            return $instance;
        });
    }

    /**
     * All pending instances where $actor is eligible to act right now —
     * the "awaiting my approval" list every approval UI needs.
     *
     * @return Collection<int, ApprovalInstance>
     */
    public function awaitingActionBy(User $actor): Collection
    {
        return ApprovalInstance::query()
            ->where('status', ApprovalStatus::Pending)
            ->where('requester_id', '!=', $actor->id)
            ->with(['steps', 'requester', 'subject'])
            ->get()
            ->filter(fn (ApprovalInstance $instance) => $this->canAct($instance, $actor))
            ->values();
    }
}
