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
        Generic app shell for Phase 0. Each portal (Employee, Supervisor, HR Admin,
        Payroll, Finance, Procurement, Programs, Super Admin — see docs/03-roles-and-permissions.md)
        gets its own sidebar navigation built in later phases; this shell just proves
        the sidebar + content layout pattern and the Livewire/Tailwind stack work
        together end to end (Build Plan Step 0.2).
    --}}
    <div class="flex min-h-screen flex-col md:flex-row">

        <aside class="w-full shrink-0 border-b border-slate-200 bg-white p-4 md:w-64 md:border-b-0 md:border-r md:p-6">
            <div class="mb-6">
                <p class="text-sm font-extrabold tracking-tight text-slate-900">{{ config('app.name') }}</p>
                <p class="text-xs text-slate-500">Planning phase build</p>
            </div>
            <nav class="space-y-1 text-sm">
                <span class="block rounded-lg bg-indigo-50 px-3 py-2 font-semibold text-indigo-700">Dashboard</span>
                <span class="block rounded-lg px-3 py-2 text-slate-400">HRM &mdash; not built yet</span>
                <span class="block rounded-lg px-3 py-2 text-slate-400">Finance &mdash; not built yet</span>
                <span class="block rounded-lg px-3 py-2 text-slate-400">Procurement &amp; Logistics &mdash; not built yet</span>
                <span class="block rounded-lg px-3 py-2 text-slate-400">Programs &mdash; not built yet</span>
            </nav>
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
