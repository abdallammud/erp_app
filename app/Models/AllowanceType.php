<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Support\Hrm\PayComponentCalculationType;
use Database\Factories\AllowanceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tenant-defined allowance (housing, transport, hardship, risk,
 * communication, per diem, ...) — see docs/04-module-hrm.md §C. Data
 * model only in Step 1.1; a config UI and actual payroll consumption
 * arrive with Step 1.5 (Payroll & Compensation) — see
 * docs/build/00-build-plan.md.
 */
#[Fillable(['tenant_id', 'name', 'code', 'calculation_type', 'amount_or_rate', 'taxable', 'is_active'])]
class AllowanceType extends Model
{
    /** @use HasFactory<AllowanceTypeFactory> */
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'calculation_type' => PayComponentCalculationType::class,
            'amount_or_rate' => 'decimal:4',
            'taxable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
