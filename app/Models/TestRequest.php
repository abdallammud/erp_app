<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\TestRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A deliberately trivial approval "subject" — proves the engine
 * end-to-end without a real business object. See this model's migration
 * and docs/build/00-build-plan.md Step 0.6's Definition of Done. Not
 * meant to survive into Phase 1 — real modules (LeaveRequest, etc.)
 * follow this exact same subject shape.
 */
#[Fillable(['tenant_id', 'requester_id', 'title', 'reason'])]
class TestRequest extends Model
{
    /** @use HasFactory<TestRequestFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * @return MorphOne<ApprovalInstance, $this>
     */
    public function approvalInstance(): MorphOne
    {
        return $this->morphOne(ApprovalInstance::class, 'subject');
    }
}
