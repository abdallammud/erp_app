<div class="space-y-6">
    @php($employee = $this->employee)

    {{-- Profile header card — blue gradient, matching the reference's
         Home screen exactly. Falls back to a plain state for accounts
         with no linked Employee record (e.g. Super Admin). --}}
    <div class="rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-white/20">
                    <x-icon name="user" class="h-8 w-8 text-white" />
                </div>
                <div>
                    <p class="text-xl font-bold">{{ $employee?->fullName() ?? auth()->user()->name }}</p>
                    @if ($employee?->position)
                        <p class="mt-0.5 flex items-center gap-1.5 text-sm text-blue-100">
                            <x-icon name="briefcase" class="h-4 w-4" />
                            {{ $employee->position->title }}
                        </p>
                    @endif
                    @if ($employee?->dutyStation)
                        <p class="mt-0.5 flex items-center gap-1.5 text-sm text-blue-100">
                            <x-icon name="building" class="h-4 w-4" />
                            {{ $employee->dutyStation->name }}
                        </p>
                    @endif
                    @if ($employee)
                        <p class="mt-1 text-xs text-blue-200">
                            ID: {{ $employee->employee_number }}
                            @if ($employee->department) &middot; Department: {{ $employee->department->name }} @endif
                        </p>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs text-blue-200">Member since</p>
                <p class="text-sm font-semibold">{{ ($employee?->hire_date ?? auth()->user()->created_at)->toFormattedDateString() }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <x-stat-card label="Pending Approvals" icon="clipboard-check" color="purple" :value="$this->pendingApprovalsCount" subtext="Awaiting your action" />
        <x-stat-card label="Notifications" icon="bell" color="blue" :value="$this->unreadNotificationsCount" subtext="Unread" />
        <x-stat-card label="My Documents" icon="cube" color="green" :value="$this->documentsCount" subtext="On file" />
        <x-stat-card label="Dependents" icon="users" color="orange" :value="$this->dependentsCount" subtext="Registered" />
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Quick Actions</h2>
        <p class="mt-1 text-sm text-slate-500">Real screens, not placeholders — see docs/build/00-build-plan.md for what's live so far.</p>
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['route' => 'my-profile', 'label' => 'My Profile', 'icon' => 'user'],
                ['route' => 'approvals-demo', 'label' => 'Approval Engine', 'icon' => 'shield-check'],
                ['route' => 'notifications', 'label' => 'Notifications', 'icon' => 'bell'],
                ['route' => 'my-profile', 'label' => 'My Documents', 'icon' => 'cube'],
            ] as $action)
                <a href="{{ route($action['route']) }}" class="flex flex-col items-center gap-2 rounded-lg border border-slate-200 p-4 text-center hover:bg-slate-50">
                    <x-icon :name="$action['icon']" class="h-5 w-5 text-slate-500" />
                    <span class="text-xs font-medium text-slate-700">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Platform Status</h2>
        <p class="mt-1 text-sm text-slate-500">
            Phase 1 in progress — see
            <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">docs/build/00-build-plan.md</code>.
        </p>
        <div class="mt-4">
            @livewire('system-status')
        </div>
    </div>
</div>
