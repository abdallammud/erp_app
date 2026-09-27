<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The "Employment" tile on My Profile — Build Plan Step 1.2. Read-only:
 * shows the signed-in user's own Employee record (department, position,
 * duty station, staff category, contract history). Showing an employee
 * their OWN salary grade is allowed even though it's Restricted-tier
 * data (docs/03-roles-and-permissions.md — "Owner + ... only", and this
 * is always exactly the record's own owner viewing it, never anyone
 * else's). Editing lives on the HR-facing /employees screen
 * (App\Livewire\Hrm\Employees) — not duplicated here.
 */
class Employment extends Component
{
    #[Computed]
    public function employee(): ?Employee
    {
        return Auth::user()->employee?->load(['department', 'position', 'dutyStation', 'contracts.salaryGrade']);
    }

    public function render()
    {
        return view('livewire.employees.employment');
    }
}
