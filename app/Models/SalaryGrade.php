<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\SalaryGradeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant-configurable pay band — see docs/04-module-hrm.md §C ("grade →
 * base salary band") and docs/build/00-build-plan.md Step 1.1. A
 * Contract links to exactly one of these; Position.grade stays a
 * separate, free-text org-structure label (Phase 0 Step 0.5) —
 * deliberately not merged with this table, see
 * docs/build/DECISIONS.md D-036.
 */
#[Fillable(['tenant_id', 'name', 'code', 'min_salary', 'max_salary', 'currency', 'is_active'])]
class SalaryGrade extends Model
{
    /** @use HasFactory<SalaryGradeFactory> */
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'min_salary' => 'decimal:2',
            'max_salary' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Contract, $this>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
