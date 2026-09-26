<x-layouts.app title="Audit Log">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
            <x-icon name="history" class="h-5 w-5" />
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Audit Log</h1>
            <p class="text-sm text-slate-500">
                Build Plan Step 0.8 — every create, update, and delete on a tenant-scoped record.
            </p>
        </div>
    </div>

    <div class="mt-6">
        @livewire('audit-log.index')
    </div>
</x-layouts.app>
