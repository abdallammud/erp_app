<x-layouts.app title="My Profile">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">My Profile</h1>
            <p class="text-sm text-slate-500">{{ auth()->user()->email }}</p>
        </div>
        <div class="flex flex-wrap gap-1.5">
            @foreach (auth()->user()->getRoleNames() as $role)
                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">{{ $role }}</span>
            @endforeach
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Organization</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ auth()->user()->tenant?->name ?? '— (Super Admin, no tenant)' }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Member since</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ auth()->user()->created_at->toFormattedDateString() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Email status</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">
                {{ auth()->user()->hasVerifiedEmail() ? 'Verified' : 'Not verified' }}
            </p>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Edit Profile</h2>
        <p class="mt-1 text-sm text-slate-500">Name and email — changes here are real, not a placeholder.</p>
        <div class="mt-4 max-w-lg">
            <livewire:profile.update-profile-information-form />
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-2">
            <x-icon name="cube" class="h-4 w-4 text-slate-400" />
            <h3 class="text-sm font-semibold text-slate-700">Documents</h3>
        </div>
        <p class="mt-1 text-xs text-slate-500">
            Build Plan Step 1.2 — ID, contract, certificates, expiry-tracked, attached to your employee record.
        </p>
        <div class="mt-4">
            @livewire('employees.documents')
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-2">
            <x-icon name="user" class="h-4 w-4 text-slate-400" />
            <h3 class="text-sm font-semibold text-slate-700">Personal Details</h3>
        </div>
        <p class="mt-1 text-xs text-slate-500">
            Build Plan Step 1.2 — contact information, dependents, emergency contacts, insurance beneficiaries.
        </p>
        <div class="mt-4">
            @livewire('employees.personal-details')
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-2">
            <x-icon name="briefcase" class="h-4 w-4 text-slate-400" />
            <h3 class="text-sm font-semibold text-slate-700">Employment</h3>
        </div>
        <p class="mt-1 text-xs text-slate-500">
            Build Plan Step 1.2 — department, position, duty station, and contract history.
        </p>
        <div class="mt-4">
            @livewire('employees.employment')
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-2">
            <x-icon name="history" class="h-4 w-4 text-slate-400" />
            <h3 class="text-sm font-semibold text-slate-700">History</h3>
        </div>
        <p class="mt-1 text-xs text-slate-500">
            Build Plan Step 1.2 — every change to your profile and employee record, fully audited.
        </p>
        <div class="mt-4">
            @livewire('employees.history')
        </div>
    </div>
</x-layouts.app>
