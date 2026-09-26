<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Approvals\ApprovalStatus;
use Database\Factories\ApprovalInstanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One specific request's progress through an ApprovalChain — see
 * docs/build/00-build-plan.md Step 0.6. Use
 * App\Support\Approvals\ApprovalWorkflow to create/act on these rather
 * than constructing them directly — it enforces segregation of duties
 * and keeps step-sequencing correct.
 */
#[Fillable(['tenant_id', 'approval_chain_id', 'requester_id', 'subject_type', 'subject_id', 'status', 'current_sequence'])]
class ApprovalInstance extends Model
{
    /** @use HasFactory<ApprovalInstanceFactory> */
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return ['status' => ApprovalStatus::class];
    }

    /**
     * @return BelongsTo<ApprovalChain, $this>
     */
    public function chain(): BelongsTo
    {
        return $this->belongsTo(ApprovalChain::class, 'approval_chain_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<ApprovalInstanceStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalInstanceStep::class)->orderBy('sequence');
    }

    public function currentStep(): ?ApprovalInstanceStep
    {
        if ($this->current_sequence === null) {
            return null;
        }

        return $this->steps->firstWhere('sequence', $this->current_sequence);
    }
}
