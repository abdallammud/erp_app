<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Support\Hrm\ContractStatus;
use App\Support\Hrm\ContractType;
use Database\Factories\ContractFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One employment period for one Employee — see
 * docs/04-module-hrm.md §B's contract lifecycle management. Renewal
 * history is just multiple Contract rows per employee, ordered by
 * start_date (see Employee::contracts()) — no separate renewal table.
 */
#[Fillable(['tenant_id', 'employee_id', 'type', 'salary_grade_id', 'start_date', 'end_date', 'status', 'notes'])]
class Contract extends Model
{
    /** @use HasFactory<ContractFactory> */
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => ContractType::class,
            'status' => ContractStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<SalaryGrade, $this>
     */
    public function salaryGrade(): BelongsTo
    {
        return $this->belongsTo(SalaryGrade::class);
    }
}
