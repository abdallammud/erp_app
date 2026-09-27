<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Support\Hrm\EmployeeStatus;
use App\Support\Hrm\Gender;
use App\Support\Hrm\StaffCategory;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A staff member — the anchor record for HRM (docs/08-data-model.md).
 * Deliberately separate from User: "not every historical employee needs
 * a login" (docs/build/00-build-plan.md Step 1.1) — `user_id` is
 * nullable, and an Employee's own name/contact fields are independent
 * of whatever User account (if any) they're linked to.
 */
#[Fillable([
    'tenant_id', 'user_id', 'employee_number', 'first_name', 'last_name',
    'gender', 'date_of_birth', 'national_id', 'phone', 'personal_email', 'address',
    'department_id', 'position_id', 'duty_station_id', 'reports_to_id',
    'staff_category', 'status', 'hire_date', 'exit_date',
])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use Auditable, BelongsToTenant, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'date_of_birth' => 'date',
            // Per docs/02-architecture.md's NFR: "encrypted DB fields for
            // sensitive data (national ID, ...)" — Laravel's native
            // encrypted cast, transparent to every read/write in the app.
            'national_id' => 'encrypted',
            'staff_category' => StaffCategory::class,
            'status' => EmployeeStatus::class,
            'hire_date' => 'date',
            'exit_date' => 'date',
        ];
    }

    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Position, $this>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * @return BelongsTo<DutyStation, $this>
     */
    public function dutyStation(): BelongsTo
    {
        return $this->belongsTo(DutyStation::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reports_to_id');
    }

    /**
     * @return HasMany<Contract, $this>
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class)->orderByDesc('start_date');
    }

    public function currentContract(): ?Contract
    {
        return $this->contracts->first();
    }

    /**
     * @return HasMany<Dependent, $this>
     */
    public function dependents(): HasMany
    {
        return $this->hasMany(Dependent::class);
    }

    /**
     * @return MorphMany<Document, $this>
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
