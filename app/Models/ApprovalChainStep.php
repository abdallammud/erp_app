<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ApprovalChainStepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One step in an ApprovalChain template — "whoever holds this role
 * approves this step." See docs/build/00-build-plan.md Step 0.6.
 */
#[Fillable(['tenant_id', 'sequence', 'approver_role', 'label'])]
class ApprovalChainStep extends Model
{
    /** @use HasFactory<ApprovalChainStepFactory> */
    use BelongsToTenant, HasFactory;

    /**
     * @return BelongsTo<ApprovalChain, $this>
     */
    public function chain(): BelongsTo
    {
        return $this->belongsTo(ApprovalChain::class, 'approval_chain_id');
    }
}
