<x-layouts.app title="Organization">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Organization</h1>
    <p class="mt-1 text-sm text-slate-500">
        Departments, duty stations, and positions — tenant-defined configuration, no code changes needed.
    </p>

    <div class="mt-6 space-y-6">
        @livewire('organization.departments')
        @livewire('organization.duty-stations')
        @livewire('organization.positions')
    </div>
</x-layouts.app>
