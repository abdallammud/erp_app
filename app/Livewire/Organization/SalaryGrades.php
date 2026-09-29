<?php

namespace App\Livewire\Organization;

use App\Models\SalaryGrade;
use App\Support\Authorization\Permission;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Tenant-configurable pay bands — Build Plan Step 1.1. Same CRUD shape
 * as Departments/DutyStations/Positions (Step 0.5); a Contract picks
 * one of these, satisfying Step 1.1's "an employee can be created with
 * a contract and a salary grade, entirely through UI" DoD.
 */
class SalaryGrades extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $code = '';

    public string $minSalary = '';

    public string $maxSalary = '';

    public string $currency = 'USD';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'minSalary' => 'required|numeric|min:0',
            'maxSalary' => 'required|numeric|gte:minSalary',
            'currency' => 'required|string|size:3',
        ];
    }

    #[Computed]
    public function salaryGrades(): Collection
    {
        return SalaryGrade::orderBy('name')->get();
    }

    public function openCreate(): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        $this->reset(['editingId', 'name', 'code', 'minSalary', 'maxSalary']);
        $this->currency = 'USD';
        $this->resetErrorBag();
        $this->dispatch('open-modal', name: 'salary-grade-form');
    }

    public function edit(int $id): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        $grade = SalaryGrade::findOrFail($id);

        $this->editingId = $grade->id;
        $this->name = $grade->name;
        $this->code = $grade->code;
        $this->minSalary = (string) $grade->min_salary;
        $this->maxSalary = (string) $grade->max_salary;
        $this->currency = $grade->currency;

        $this->dispatch('open-modal', name: 'salary-grade-form');
    }

    public function save(): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        $validated = $this->validate();

        $attributes = [
            'name' => $validated['name'],
            'code' => $validated['code'],
            'min_salary' => $validated['minSalary'],
            'max_salary' => $validated['maxSalary'],
            'currency' => strtoupper($validated['currency']),
        ];

        if ($this->editingId) {
            SalaryGrade::findOrFail($this->editingId)->update($attributes);
        } else {
            SalaryGrade::create($attributes);
        }

        unset($this->salaryGrades);
        $this->reset(['editingId', 'name', 'code', 'minSalary', 'maxSalary']);
        $this->currency = 'USD';
        $this->dispatch('close-modal', name: 'salary-grade-form');
    }

    public function delete(int $id): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        SalaryGrade::findOrFail($id)->delete();

        unset($this->salaryGrades);
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'code', 'minSalary', 'maxSalary']);
        $this->currency = 'USD';
        $this->resetErrorBag();
        $this->dispatch('close-modal', name: 'salary-grade-form');
    }

    public function render()
    {
        return view('livewire.organization.salary-grades');
    }
}
