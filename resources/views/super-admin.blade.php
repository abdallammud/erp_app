<x-layouts.app title="Super Admin Portal">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
            <x-icon name="building" class="h-5 w-5" />
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Super Admin Portal</h1>
            <p class="text-sm text-slate-500">
                Build Plan Step 0.11 — outside every tenant by design. No default access to any
                tenant's business data; impersonation for support is logged.
            </p>
        </div>
    </div>

    <div class="mt-6">
        @livewire('super-admin.dashboard')
    </div>
</x-layouts.app>
