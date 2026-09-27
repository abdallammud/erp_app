<x-layouts.app title="My Profile">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">My Profile</h1>
            <p class="text-sm text-slate-500">View and manage your personal information</p>
        </div>
        <div class="flex flex-wrap gap-1.5">
            @foreach (auth()->user()->getRoleNames() as $role)
                <x-badge variant="info">{{ $role }}</x-badge>
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

    {{--
        Tabbed, matching the Nova HRM reference's My Profile structure
        exactly (Edit Profile | Personal Details | Employment |
        Documents | History) — see docs/build/DESIGN.md. Every tab's
        Livewire component mounts on page load; Alpine only toggles
        which one is visible, so switching tabs is instant with no
        extra request.
    --}}
    <div x-data="{ tab: 'edit' }" class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <nav class="flex gap-6 overflow-x-auto border-b border-slate-200 px-6">
            @foreach ([
                ['key' => 'edit', 'label' => 'Edit Profile', 'icon' => 'user'],
                ['key' => 'personal', 'label' => 'Personal Details', 'icon' => 'user'],
                ['key' => 'employment', 'label' => 'Employment', 'icon' => 'briefcase'],
                ['key' => 'documents', 'label' => 'Documents', 'icon' => 'cube'],
                ['key' => 'history', 'label' => 'History', 'icon' => 'history'],
            ] as $tab)
                <button
                    type="button"
                    x-on:click="tab = '{{ $tab['key'] }}'"
                    :class="tab === '{{ $tab['key'] }}' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="flex shrink-0 items-center gap-1.5 border-b-2 py-3.5 text-sm font-medium"
                >
                    <x-icon :name="$tab['icon']" class="h-4 w-4" />
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>

        <div class="p-6">
            <div x-show="tab === 'edit'" x-cloak>
                <h2 class="text-base font-semibold text-slate-900">Edit Profile</h2>
                <p class="mt-1 text-sm text-slate-500">Name and email — changes here are real, not a placeholder.</p>
                <div class="mt-4 max-w-lg">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div x-show="tab === 'personal'" x-cloak>
                @livewire('employees.personal-details')
            </div>

            <div x-show="tab === 'employment'" x-cloak>
                @livewire('employees.employment')
            </div>

            <div x-show="tab === 'documents'" x-cloak>
                @livewire('employees.documents')
            </div>

            <div x-show="tab === 'history'" x-cloak>
                @livewire('employees.history')
            </div>
        </div>
    </div>
</x-layouts.app>
