<?php

namespace App\Livewire\Organization;

use App\Models\Department;
use App\Models\Position;
use App\Support\Authorization\Permission;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Simple CRUD for the tenant's positions (job title/grade slots) — Build
 * Plan Step 0.5. Anyone with `hrm.org.view` sees this; only
 * `hrm.org.edit` can mutate.
 */
class Positions extends Component
{
    public ?int $editingId = null;

    public string $title = '';

    public ?string $grade = null;

    public ?int $departmentId = null;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'grade' => 'nullable|string|max:50',
            'departmentId' => 'nullable|exists:departments,id',
        ];
    }

    #[Computed]
    public function positions(): Collection
    {
        return Position::with('department')->orderBy('title')->get();
    }

    #[Computed]
    public function departmentOptions(): Collection
    {
        return Department::orderBy('name')->get();
    }

    public function edit(int $id): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        $position = Position::findOrFail($id);

        $this->editingId = $position->id;
        $this->title = $position->title;
        $this->grade = $position->grade;
        $this->departmentId = $position->department_id;
    }

    public function save(): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        $validated = $this->validate();
        $validated['department_id'] = $validated['departmentId'];
        unset($validated['departmentId']);

        if ($this->editingId) {
            Position::findOrFail($this->editingId)->update($validated);
        } else {
            Position::create($validated);
        }

        unset($this->positions);
        $this->reset(['editingId', 'title', 'grade', 'departmentId']);
    }

    public function delete(int $id): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        Position::findOrFail($id)->delete();

        unset($this->positions);
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'title', 'grade', 'departmentId']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.organization.positions');
    }
}
