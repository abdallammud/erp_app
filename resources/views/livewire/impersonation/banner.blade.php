<div>
    {{-- Livewire requires a single root element from every component's
         render output, even when there's nothing to show — the @if
         must live INSIDE this wrapper, not wrap it, or every page using
         this component 500s the moment $isImpersonating is false (the
         common case). Found by the test suite before it ever reached a
         live page. --}}
    @if ($isImpersonating)
        <div class="flex items-center justify-between gap-3 bg-amber-500 px-4 py-2 text-xs font-medium text-white">
            <span>You're impersonating {{ auth()->user()->name }} for support. This is logged.</span>
            <button wire:click="stop" class="shrink-0 rounded bg-amber-600 px-2 py-1 font-semibold hover:bg-amber-700">
                Stop impersonating
            </button>
        </div>
    @endif
</div>
