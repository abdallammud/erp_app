<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Approvals\ApprovalStepStatus;
use Database\Factories\ApprovalInstanceStepFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The concrete record of one step's progress within an ApprovalInstance —
 * see docs/build/00-build-plan.md Step 0.6.
 */
#[Fillable(['tenant_id', 'sequence', 'approver_role', 'label', 'status', 'acted_by', 'acted_at', 'comment'])]
class ApprovalInstanceStep extends Model
{
    /** @use HasFactory<ApprovalInstanceStepFactory> */
    use BelongsToTenant, HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ApprovalStepStatus::class,
            'acted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ApprovalInstance, $this>
     */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(ApprovalInstance::class, 'approval_instance_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
