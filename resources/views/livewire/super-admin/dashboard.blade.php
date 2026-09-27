<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif

    <div>
        <h2 class="text-base font-semibold text-slate-900">System health</h2>
        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @php
                $health = $this->systemHealth;
                $storageMb = number_format($health['total_storage_bytes'] / 1048576, 2);
            @endphp
            @foreach ([
                ['label' => 'Active tenants', 'value' => $health['tenants_active']],
                ['label' => 'Suspended tenants', 'value' => $health['tenants_suspended']],
                ['label' => 'Total users', 'value' => $health['total_users']],
                ['label' => 'Pending jobs', 'value' => $health['pending_jobs']],
                ['label' => 'Failed jobs (24h)', 'value' => $health['failed_jobs_24h']],
                ['label' => 'Document storage', 'value' => $storageMb.' MB'],
            ] as $stat)
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                    <p class="text-xs text-slate-500">{{ $stat['label'] }}</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Create a new tenant</h2>
        <p class="mt-1 text-sm text-slate-500">
            Creates the organization and its first admin account together — an empty tenant with no
            users would exist but nobody could ever log into it.
        </p>
        <form wire:submit="createTenant" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="block text-xs font-medium text-slate-600">Organization name</label>
                <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div></div>
            <div>
                <label class="block text-xs font-medium text-slate-600">Admin name</label>
                <input type="text" wire:model="adminName" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                @error('adminName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600">Admin email</label>
                <input type="email" wire:model="adminEmail" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                @error('adminEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600">Temporary password (share this with them)</label>
                <input type="text" wire:model="adminPassword" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                @error('adminPassword') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end">
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800">
                    Create tenant
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <h2 class="border-b border-slate-100 px-5 py-3 text-base font-semibold text-slate-900">Tenants</h2>
        @forelse ($this->tenants as $tenant)
            <div wire:key="{{ $tenant->id }}" class="border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm font-semibold text-slate-900">{{ $tenant->name }}</span>
                        <span class="text-xs text-slate-400">{{ $tenant->slug }}</span>
                        @if ($tenant->is_active)
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Active</span>
                        @else
                            <span class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-700">Suspended</span>
                        @endif
                        <span class="text-xs text-slate-400">{{ $tenant->users_count }} user{{ $tenant->users_count === 1 ? '' : 's' }}</span>
                    </div>
                    <button wire:click="toggleSuspend({{ $tenant->id }})"
                            wire:confirm="{{ $tenant->is_active ? 'Suspend this tenant? Its users will not be able to log in.' : 'Reactivate this tenant?' }}"
                            class="text-xs font-semibold {{ $tenant->is_active ? 'text-red-600 hover:text-red-500' : 'text-emerald-600 hover:text-emerald-500' }}">
                        {{ $tenant->is_active ? 'Suspend' : 'Reactivate' }}
                    </button>
                </div>

                @if ($tenant->users_count > 0)
                    <ul class="mt-3 space-y-1">
                        @foreach ($this->usersFor($tenant->id) as $user)
                            <li class="flex items-center justify-between gap-2 text-xs">
                                <span class="text-slate-600">{{ $user->name }} <span class="text-slate-400">({{ $user->email }})</span></span>
                                <button wire:click="impersonate({{ $user->id }})"
                                        wire:confirm="Impersonate {{ $user->name }}? This is logged."
                                        class="font-semibold text-blue-600 hover:text-blue-500">
                                    Impersonate
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @empty
            <p class="px-5 py-10 text-center text-sm text-slate-400">No tenants yet.</p>
        @endforelse
    </div>
</div>
