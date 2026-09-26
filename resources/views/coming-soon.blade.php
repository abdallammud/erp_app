<x-layouts.app :title="$feature">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
            <x-icon :name="$icon ?? 'cube'" class="h-5 w-5" />
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $feature }}</h1>
            <p class="text-sm text-slate-500">Part of the HRM module.</p>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
        <p class="text-sm font-semibold text-slate-900">Coming in Phase 1</p>
        <p class="mx-auto mt-1 max-w-md text-sm text-slate-500">
            This screen is in the plan (see
            <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">docs/04-module-hrm.md</code>)
            but not built yet — Phase 0 is the platform foundation (tenancy, auth, RBAC), still in progress.
            Track real status in
            <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">docs/build/00-build-plan.md</code>.
        </p>
    </div>
</x-layouts.app>
