<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-900">Platform status</h2>
        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
            {{ ucfirst($environment) }}
        </span>
    </div>

    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
        <div>
            <dt class="text-slate-500">Laravel</dt>
            <dd class="font-medium text-slate-900">{{ $laravelVersion }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">PHP</dt>
            <dd class="font-medium text-slate-900">{{ $phpVersion }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Server time</dt>
            <dd class="font-medium text-slate-900">{{ $now->toDayDateTimeString() }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Component refreshes</dt>
            <dd class="font-medium text-slate-900">{{ $refreshes }}</dd>
        </div>
    </dl>

    <button
        type="button"
        wire:click="refresh"
        class="mt-5 inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
    >
        Refresh
    </button>
</div>
