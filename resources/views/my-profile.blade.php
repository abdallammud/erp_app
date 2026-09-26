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

    @foreach ([
        ['title' => 'Personal Details', 'icon' => 'user', 'note' => 'Dependents, emergency contacts, insurance beneficiaries — see docs/04-module-hrm.md §B.'],
        ['title' => 'Employment', 'icon' => 'briefcase', 'note' => 'Contract, position, grade, duty station, employment history — see docs/04-module-hrm.md §B.'],
        ['title' => 'Documents', 'icon' => 'cube', 'note' => 'ID, contract, certificates — expiry-tracked, verified by HR. See docs/04-module-hrm.md §B.'],
        ['title' => 'History', 'icon' => 'history', 'note' => 'Employment and profile change history, fully audited. See docs/04-module-hrm.md §B.'],
    ] as $section)
        <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white p-6">
            <div class="flex items-center gap-2">
                <x-icon :name="$section['icon']" class="h-4 w-4 text-slate-400" />
                <h3 class="text-sm font-semibold text-slate-700">{{ $section['title'] }}</h3>
                <span class="ml-auto text-xs font-medium text-slate-400">Coming in Phase 1</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">{{ $section['note'] }}</p>
        </div>
    @endforeach
</x-layouts.app>
