<?php

namespace App\Livewire\Hrm;

use App\Models\Contract;
use App\Models\Department;
use App\Models\DutyStation;
use App\Models\Employee;
use App\Models\Position;
use App\Models\SalaryGrade;
use App\Models\User;
use App\Support\Authorization\Permission;
use App\Support\Hrm\ContractStatus;
use App\Support\Hrm\ContractType;
use App\Support\Hrm\EmployeeStatus;
use App\Support\Hrm\Gender;
use App\Support\Hrm\StaffCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The anchor HRM screen — Build Plan Step 1.1. Creating an employee also
 * creates their first Contract in the same action (a SalaryGrade is
 * required for it) — an employee record with no contract wouldn't
 * satisfy this step's DoD ("an employee can be created with a contract
 * and a salary grade, entirely through UI, no seeders needed").
 *
 * Editing an existing employee only touches the employee's own fields,
 * not their contract — contract renewal/amendment is real Step 1.5
 * (Payroll & Compensation) scope, not built here; see this class's
 * `render()`.
 */
class Employees extends Component
{
    public ?int $editingId = null;

    public string $employeeNumber = '';

    public string $firstName = '';

    public string $lastName = '';

    public ?string $gender = null;

    public ?string $dateOfBirth = null;

    public ?string $nationalId = null;

    public ?string $phone = null;

    public ?string $personalEmail = null;

    public ?string $address = null;

    public ?int $departmentId = null;

    public ?int $positionId = null;

    public ?int $dutyStationId = null;

    public ?int $reportsToId = null;

    public ?int $userId = null;

    public string $staffCategory = '';

    public string $hireDate = '';

    // Only used on create — see this class's docblock.
    public string $contractType = '';

    public ?int $salaryGradeId = null;

    public string $contractStartDate = '';

    public ?string $contractEndDate = null;

    protected function rules(): array
    {
        $rules = [
            'employeeNumber' => ['required', 'string', 'max:255', Rule::unique('employees', 'employee_number')->ignore($this->editingId)],
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'gender' => 'nullable|in:'.implode(',', array_column(Gender::cases(), 'value')),
            'dateOfBirth' => 'nullable|date',
            'nationalId' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'personalEmail' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'departmentId' => 'nullable|exists:departments,id',
            'positionId' => 'nullable|exists:positions,id',
            'dutyStationId' => 'nullable|exists:duty_stations,id',
            'reportsToId' => 'nullable|exists:employees,id',
            'userId' => 'nullable|exists:users,id',
            'staffCategory' => 'required|in:'.implode(',', array_column(StaffCategory::cases(), 'value')),
            'hireDate' => 'required|date',
        ];

        if (! $this->editingId) {
            $rules += [
                'contractType' => 'required|in:'.implode(',', array_column(ContractType::cases(), 'value')),
                'salaryGradeId' => 'required|exists:salary_grades,id',
                'contractStartDate' => 'required|date',
                'contractEndDate' => 'nullable|date|after_or_equal:contractStartDate',
            ];
        }

        return $rules;
    }

    #[Computed]
    public function employees(): Collection
    {
        return Employee::with(['department', 'position', 'dutyStation', 'contracts.salaryGrade'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    #[Computed]
    public function departments(): Collection
    {
        return Department::orderBy('name')->get();
    }

    #[Computed]
    public function positions(): Collection
    {
        return Position::orderBy('title')->get();
    }

    #[Computed]
    public function dutyStations(): Collection
    {
        return DutyStation::orderBy('name')->get();
    }

    #[Computed]
    public function salaryGrades(): Collection
    {
        return SalaryGrade::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function linkableUsers(): Collection
    {
        return User::whereDoesntHave('employee')
            ->orWhere('id', $this->userId)
            ->orderBy('name')
            ->get();
    }

    public function edit(int $id): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        $employee = Employee::findOrFail($id);

        $this->editingId = $employee->id;
        $this->employeeNumber = $employee->employee_number;
        $this->firstName = $employee->first_name;
        $this->lastName = $employee->last_name;
        $this->gender = $employee->gender?->value;
        $this->dateOfBirth = $employee->date_of_birth?->toDateString();
        $this->nationalId = $employee->national_id;
        $this->phone = $employee->phone;
        $this->personalEmail = $employee->personal_email;
        $this->address = $employee->address;
        $this->departmentId = $employee->department_id;
        $this->positionId = $employee->position_id;
        $this->dutyStationId = $employee->duty_station_id;
        $this->reportsToId = $employee->reports_to_id;
        $this->userId = $employee->user_id;
        $this->staffCategory = $employee->staff_category->value;
        $this->hireDate = $employee->hire_date->toDateString();
    }

    public function save(): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        $validated = $this->validate();

        $employeeAttributes = [
            'employee_number' => $validated['employeeNumber'],
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['dateOfBirth'],
            'national_id' => $validated['nationalId'],
            'phone' => $validated['phone'],
            'personal_email' => $validated['personalEmail'],
            'address' => $validated['address'],
            'department_id' => $validated['departmentId'],
            'position_id' => $validated['positionId'],
            'duty_station_id' => $validated['dutyStationId'],
            'reports_to_id' => $validated['reportsToId'],
            'user_id' => $validated['userId'],
            'staff_category' => $validated['staffCategory'],
            'hire_date' => $validated['hireDate'],
        ];

        if ($this->editingId) {
            Employee::findOrFail($this->editingId)->update($employeeAttributes);
        } else {
            DB::transaction(function () use ($employeeAttributes, $validated): void {
                $employee = Employee::create($employeeAttributes + ['status' => EmployeeStatus::Active->value]);

                Contract::create([
                    'employee_id' => $employee->id,
                    'type' => $validated['contractType'],
                    'salary_grade_id' => $validated['salaryGradeId'],
                    'start_date' => $validated['contractStartDate'],
                    'end_date' => $validated['contractEndDate'],
                    'status' => ContractStatus::Active->value,
                ]);
            });
        }

        unset($this->employees, $this->linkableUsers);
        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->resetErrorBag();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'employeeNumber', 'firstName', 'lastName', 'gender', 'dateOfBirth',
            'nationalId', 'phone', 'personalEmail', 'address', 'departmentId', 'positionId',
            'dutyStationId', 'reportsToId', 'userId', 'staffCategory', 'hireDate',
            'contractType', 'salaryGradeId', 'contractStartDate', 'contractEndDate',
        ]);
    }

    public function render()
    {
        return view('livewire.hrm.employees');
    }
}
