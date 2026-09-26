<x-layouts.app title="Approval Engine Demo">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
            <x-icon name="shield-check" class="h-5 w-5" />
        </div>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Approval Engine Demo</h1>
            <p class="text-sm text-slate-500">
                Build Plan Step 0.6 — a real, working proof, not a mockup. See
                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">docs/build/00-build-plan.md</code>.
            </p>
        </div>
    </div>

    <div class="mt-6">
        @livewire('approvals.demo')
    </div>
</x-layouts.app>
