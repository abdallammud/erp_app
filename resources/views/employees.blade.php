<x-layouts.app title="Employees">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Employees</h1>
    <p class="mt-1 text-sm text-slate-500">
        Build Plan Step 1.1 — the anchor HRM record. Every employee has a contract and a salary grade
        from the moment they're created. See docs/04-module-hrm.md §B.
    </p>

    <div class="mt-6">
        @livewire('hrm.employees')
    </div>
</x-layouts.app>
