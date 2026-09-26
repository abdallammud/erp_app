<x-layouts.app title="Notifications">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
            <x-icon name="bell" class="h-5 w-5" />
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Notifications</h1>
            <p class="text-sm text-slate-500">
                Build Plan Step 0.7 — approval-needed and approval-decision alerts today; more
                types land as the modules that trigger them (documents, contracts, budgets) are built.
            </p>
        </div>
    </div>

    <div class="mt-6">
        @livewire('notifications.inbox')
    </div>
</x-layouts.app>
