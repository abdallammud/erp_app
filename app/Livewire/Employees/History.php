<?php

namespace App\Livewire\Employees;

use App\Livewire\Concerns\FormatsAuditValues;
use App\Models\AuditLogEntry;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The "History" tile on My Profile — Build Plan Step 1.2's "Profile
 * view/edit with change-history logging (old/new value + approver)".
 * The logging itself already happens automatically (both User and
 * Employee use the Auditable trait from Step 0.8) — this is just the
 * self-scoped view of it, reusing App\Models\AuditLogEntry directly
 * rather than building a second audit mechanism.
 */
class History extends Component
{
    use FormatsAuditValues;

    #[Computed]
    public function entries(): Collection
    {
        $user = Auth::user();
        $employee = $user->employee;

        return AuditLogEntry::query()
            ->with('causer')
            ->where(function ($query) use ($user, $employee) {
                $query->where(fn ($q) => $q->where('subject_type', User::class)->where('subject_id', $user->id));

                if ($employee) {
                    $query->orWhere(fn ($q) => $q->where('subject_type', Employee::class)->where('subject_id', $employee->id));
                }
            })
            ->latest()
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.employees.history');
    }
}
