@props(['title', 'subtitle' => null])

<div class="mb-4 flex items-start justify-between">
    <div>
        <h2 class="text-base font-bold text-slate-900">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-0.5 text-xs text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    <button type="button" x-on:click="show = false" class="shrink-0 rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
    </button>
</div>
