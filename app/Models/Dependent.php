<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Support\Hrm\DependentRelationship;
use Database\Factories\DependentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A family member / emergency contact / insurance beneficiary for one
 * Employee — see docs/04-module-hrm.md §B and
 * docs/build/00-build-plan.md Step 1.2. Restricted-tier data
 * (docs/03-roles-and-permissions.md's confidentiality tiers —
 * "beneficiary personal data"): visible only to the employee it
 * belongs to and HR, enforced by App\Livewire\Employees\PersonalDetails
 * (self-service only in Step 1.2 — HR viewing another employee's
 * dependents is real, later Phase 1 scope, not built here).
 */
#[Fillable([
    'tenant_id', 'employee_id', 'first_name', 'last_name', 'relationship',
    'date_of_birth', 'passport_number', 'phone', 'is_emergency_contact',
    'is_insurance_beneficiary', 'insurance_beneficiary_percentage',
])]
class Dependent extends Model
{
    /** @use HasFactory<DependentFactory> */
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'relationship' => DependentRelationship::class,
            'date_of_birth' => 'date',
            // Restricted-tier personal data — see this class's docblock.
            'passport_number' => 'encrypted',
            'is_emergency_contact' => 'boolean',
            'is_insurance_beneficiary' => 'boolean',
            'insurance_beneficiary_percentage' => 'decimal:2',
        ];
    }

    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
