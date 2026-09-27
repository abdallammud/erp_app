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
    @auth
        @livewire('impersonation.banner')
    @endauth

    {{--
        Employee Portal shell — visual design follows the Nova
        Humanitarian HRM UI reference exactly (colors, top bar, sidebar
        shape, button style) as of the Step 1.2 design pass — see
        docs/build/DESIGN.md for the full token reference every future
        screen must follow. One unified nav for now, not yet the four
        distinct role portals from docs/03-roles-and-permissions.md —
        that split is real Phase 1 scope (Steps 1.12-1.14).
    --}}
    <div class="flex min-h-screen flex-col md:flex-row">

        <aside class="flex w-full shrink-0 flex-col overflow-y-auto border-b border-slate-200 bg-white p-4 md:h-screen md:w-64 md:border-b-0 md:border-r md:p-5">
            <div class="mb-5 flex items-center gap-2.5 px-1">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white">
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

                    $unreadNotifications = auth()->user()?->unreadNotifications()->count() ?? 0;
                @endphp

                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 font-medium {{ request()->routeIs($item['route']) ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <x-icon :name="$item['icon']" class="h-[18px] w-[18px] shrink-0" />
                        <span class="truncate">{{ $item['label'] }}</span>
                        @if ($item['route'] === 'notifications' && $unreadNotifications > 0)
                            <span class="ml-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1 text-[11px] font-semibold text-white">
                                {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>

            @can(\App\Support\Authorization\Permission::HrmOrgView->value)
                <div class="mt-5 border-t border-slate-100 pt-4">
                    <p class="px-2.5 text-xs font-semibold uppercase tracking-wide text-slate-400">Administration</p>
                    <nav class="mt-2 space-y-0.5 text-sm">
                        <a href="{{ route('organization') }}"
                           class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 font-medium {{ request()->routeIs('organization') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                            <x-icon name="building" class="h-[18px] w-[18px] shrink-0" />
                            <span>Organization</span>
                        </a>
                        <a href="{{ route('employees') }}"
                           class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 font-medium {{ request()->routeIs('employees') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                            <x-icon name="users" class="h-[18px] w-[18px] shrink-0" />
                            <span>Employees</span>
                        </a>
                        <a href="{{ route('audit-log') }}"
                           class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 font-medium {{ request()->routeIs('audit-log') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                            <x-icon name="history" class="h-[18px] w-[18px] shrink-0" />
                            <span>Audit Log</span>
                        </a>
                    </nav>
                </div>
            @endcan

            @can(\App\Support\Authorization\Permission::PlatformAdmin->value)
                <div class="mt-5 border-t border-slate-100 pt-4">
                    <p class="px-2.5 text-xs font-semibold uppercase tracking-wide text-slate-400">Platform</p>
                    <nav class="mt-2 space-y-0.5 text-sm">
                        <a href="{{ route('super-admin') }}"
                           class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 font-medium {{ request()->routeIs('super-admin') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                            <x-icon name="shield-check" class="h-[18px] w-[18px] shrink-0" />
                            <span>Super Admin Portal</span>
                        </a>
                    </nav>
                </div>
            @endcan

            <div class="mt-5 border-t border-slate-100 pt-4">
                <p class="px-2.5 text-xs font-semibold uppercase tracking-wide text-slate-400">Engine demos</p>
                <nav class="mt-2 space-y-0.5 text-sm">
                    <a href="{{ route('approvals-demo') }}"
                       class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 font-medium {{ request()->routeIs('approvals-demo') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <x-icon name="shield-check" class="h-[18px] w-[18px] shrink-0" />
                        <span>Approval Engine</span>
                    </a>
                </nav>
            </div>

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

        <div class="flex-1">
            {{-- Persistent top bar — page title (left) + the signed-in
                 user's email + a notification bell (right), on every
                 page, matching the reference exactly. Page-level content
                 headings (with icons/descriptions/action buttons) live
                 inside $slot below, not here. --}}
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-4 md:px-10">
                <div class="min-w-0">
                    <h1 class="truncate text-xl font-bold text-slate-900">{{ $pageTitle ?? $title ?? config('app.name') }}</h1>
                    @auth
                        <p class="truncate text-sm text-slate-500">{{ auth()->user()->email }}</p>
                    @endauth
                </div>
                @auth
                    <a href="{{ route('notifications') }}" class="relative shrink-0 rounded-lg p-2 text-slate-400 hover:bg-slate-50 hover:text-slate-600">
                        <x-icon name="bell" class="h-5 w-5" />
                        @if ($unreadNotifications > 0)
                            <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-red-500"></span>
                        @endif
                    </a>
                @endauth
            </header>

            <main class="p-4 md:p-10">
                <div class="mx-auto max-w-6xl">
                    {{ $slot }}
                </div>
            </main>
        </div>

    </div>

    @livewireScripts
</body>
</html>
