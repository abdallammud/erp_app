<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-slate-900">

    {{--
        Employee Portal shell — nav items match the Nova Humanitarian HRM
        UI reference (see docs/build/DECISIONS.md D-024). One unified nav
        for now, not yet the four distinct role portals from
        docs/03-roles-and-permissions.md — that split is real Phase 1
        scope. Most items are honest "coming in Phase 1" placeholders
        (docs/04-module-hrm.md has the real spec); only Home, My Profile,
        and (for permitted roles) Organization are real screens today.
    --}}
    <div class="flex min-h-screen flex-col md:flex-row">

        <aside class="flex w-full shrink-0 flex-col overflow-y-auto border-b border-slate-200 bg-white p-4 md:h-screen md:w-64 md:border-b-0 md:border-r md:p-5">
            <div class="mb-5 flex items-center gap-2.5 px-1">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">
                    N
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-extrabold tracking-tight text-slate-900">{{ config('app.name') }}</p>
                    <p class="truncate text-xs text-slate-500">Employee Portal</p>
                </div>
            </div>

            <nav class="space-y-0.5 text-sm">
                @php
                    $navItems = [
                        ['route' => 'dashboard', 'label' => 'Home', 'icon' => 'home'],
                        ['route' => 'my-profile', 'label' => 'My Profile', 'icon' => 'user'],
                        ['route' => 'dependents', 'label' => 'Dependents', 'icon' => 'users'],
                        ['route' => 'projects', 'label' => 'Projects', 'icon' => 'briefcase'],
                        ['route' => 'leave', 'label' => 'Leave', 'icon' => 'calendar'],
                        ['route' => 'performance', 'label' => 'Performance', 'icon' => 'chart-bar'],
                        ['route' => 'self-assessment', 'label' => 'Self-Assessment', 'icon' => 'clipboard-check'],
                        ['route' => 'payroll', 'label' => 'Payroll', 'icon' => 'currency-dollar'],
                        ['route' => 'timesheet', 'label' => 'Timesheet', 'icon' => 'clock'],
                        ['route' => 'training', 'label' => 'Training', 'icon' => 'book-open'],
                        ['route' => 'assets', 'label' => 'Assets', 'icon' => 'cube'],
                        ['route' => 'asset-verification', 'label' => 'Asset Verification', 'icon' => 'clipboard-check'],
                        ['route' => 'safeguarding', 'label' => 'Safeguarding', 'icon' => 'shield-check'],
                        ['route' => 'calendar', 'label' => 'Calendar', 'icon' => 'calendar'],
                        ['route' => 'history', 'label' => 'History', 'icon' => 'history'],
                        ['route' => 'notifications', 'label' => 'Notifications', 'icon' => 'bell'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 font-medium {{ request()->routeIs($item['route']) ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <x-icon :name="$item['icon']" class="h-[18px] w-[18px] shrink-0" />
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            @can(\App\Support\Authorization\Permission::HrmOrgView->value)
                <div class="mt-5 border-t border-slate-100 pt-4">
                    <p class="px-2.5 text-xs font-semibold uppercase tracking-wide text-slate-400">Administration</p>
                    <nav class="mt-2 space-y-0.5 text-sm">
                        <a href="{{ route('organization') }}"
                           class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 font-medium {{ request()->routeIs('organization') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">
                            <x-icon name="building" class="h-[18px] w-[18px] shrink-0" />
                            <span>Organization</span>
                        </a>
                    </nav>
                </div>
            @endcan

            @auth
                <div class="mt-auto border-t border-slate-100 pt-4">
                    <p class="truncate text-sm font-medium text-slate-900">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-slate-900">
                            Log out
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        <main class="flex-1 p-4 md:p-10">
            <div class="mx-auto max-w-3xl">
                {{ $slot }}
            </div>
        </main>

    </div>

    @livewireScripts
</body>
</html>
