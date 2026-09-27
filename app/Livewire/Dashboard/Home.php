<?php

namespace App\Livewire\Dashboard;

use App\Models\Employee;
use App\Support\Approvals\ApprovalWorkflow;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The Home dashboard — visual structure (profile header card, stat
 * cards, quick actions) follows the Nova HRM reference's Home screen,
 * see docs/build/DESIGN.md. Every number shown is real data from a
 * module that actually exists; deliberately no "Leave balance" /
 * "Latest payslip" style cards the reference has, since Leave and
 * Payroll are still Phase 1 placeholders — showing fabricated numbers
 * for modules that don't exist would be dishonest, not just early.
 */
class Home extends Component
{
    #[Computed]
    public function employee(): ?Employee
    {
        return $this->resolveEmployee();
    }

    private function resolveEmployee(): ?Employee
    {
        return Auth::user()->employee?->load(['position', 'department', 'dutyStation']);
    }

    #[Computed]
    public function pendingApprovalsCount(): int
    {
        return app(ApprovalWorkflow::class)->awaitingActionBy(Auth::user())->count();
    }

    #[Computed]
    public function unreadNotificationsCount(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    #[Computed]
    public function documentsCount(): int
    {
        return $this->resolveEmployee()?->documents()->count() ?? 0;
    }

    #[Computed]
    public function dependentsCount(): int
    {
        return $this->resolveEmployee()?->dependents()->count() ?? 0;
    }

    public function render()
    {
        return view('livewire.dashboard.home');
    }
}
