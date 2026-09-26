<?php

namespace App\Livewire\Organization;

use App\Models\Department;
use App\Support\Authorization\Permission;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Simple CRUD for the tenant's departments — Build Plan Step 0.5.
 * Anyone with `hrm.org.view` sees this; only `hrm.org.edit` can mutate.
 */
class Departments extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public ?string $code = null;

    public ?int $parentDepartmentId = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'parentDepartmentId' => 'nullable|exists:departments,id',
        ];
    }

    #[Computed]
    public function departments(): Collection
    {
        return Department::with('parent')->orderBy('name')->get();
    }

    public function edit(int $id): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        $department = Department::findOrFail($id);

        $this->editingId = $department->id;
        $this->name = $department->name;
        $this->code = $department->code;
        $this->parentDepartmentId = $department->parent_department_id;
    }

    public function save(): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        $validated = $this->validate();

        // Only meaningful while editing — on create, editingId and an
        // unset parentDepartmentId are BOTH null, and null === null is
        // true, so this must not fire for a plain create with no parent
        // selected (a real bug caught by
        // tests/Feature/Organization/DepartmentsTest.php).
        if ($this->editingId !== null && $this->parentDepartmentId === $this->editingId) {
            $this->addError('parentDepartmentId', 'A department cannot be its own parent.');

            return;
        }

        $validated['parent_department_id'] = $validated['parentDepartmentId'];
        unset($validated['parentDepartmentId']);

        if ($this->editingId) {
            Department::findOrFail($this->editingId)->update($validated);
        } else {
            Department::create($validated);
        }

        unset($this->departments);
        $this->reset(['editingId', 'name', 'code', 'parentDepartmentId']);
    }

    public function delete(int $id): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        Department::findOrFail($id)->delete();

        unset($this->departments);
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'code', 'parentDepartmentId']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.organization.departments');
    }
}
