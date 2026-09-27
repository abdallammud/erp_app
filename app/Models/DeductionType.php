<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Support\Hrm\DeductionCategory;
use App\Support\Hrm\PayComponentCalculationType;
use Database\Factories\DeductionTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant-defined deduction (tax, insurance, pension, loan repayment,
 * ...) — see docs/04-module-hrm.md §C. Data model only in Step 1.1;
 * see App\Models\AllowanceType's docblock for why the config UI waits
 * for Step 1.5.
 */
#[Fillable(['tenant_id', 'name', 'code', 'category', 'calculation_type', 'amount_or_rate', 'is_active'])]
class DeductionType extends Model
{
    /** @use HasFactory<DeductionTypeFactory> */
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'category' => DeductionCategory::class,
            'calculation_type' => PayComponentCalculationType::class,
            'amount_or_rate' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }
}
