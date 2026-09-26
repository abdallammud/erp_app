<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Database\Factories\ApprovalChainFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A reusable, tenant-configured approval workflow template for one
 * action type (e.g. 'test_request', later 'leave.annual') — see
 * docs/03-roles-and-permissions.md's approval workflow engine and
 * docs/build/00-build-plan.md Step 0.6.
 */
#[Fillable(['tenant_id', 'name', 'action_type', 'is_active'])]
class ApprovalChain extends Model
{
    /** @use HasFactory<ApprovalChainFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @return HasMany<ApprovalChainStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalChainStep::class)->orderBy('sequence');
    }
}
