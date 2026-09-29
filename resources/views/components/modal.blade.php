@props(['name', 'maxWidth' => 'md'])

@php
    $widths = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl'];
@endphp

{{--
    Detail is always an object with a `name` key — this matches what
    Livewire's own `$this->dispatch(...)` produces (named params become
    the detail object), so both a Livewire action and a plain DOM/Alpine
    trigger use the exact same shape:

    Trigger from anywhere with a plain DOM event (no Alpine scope
    needed on the trigger itself):
    onclick="window.dispatchEvent(new CustomEvent('open-modal', {detail: {name: '{{ $name }}'}}))"

    Or from Alpine: $dispatch('open-modal', { name: '{{ $name }}' })

    Close from a Livewire action after a successful save by dispatching
    a browser event: $this->dispatch('close-modal', name: '{{ $name }}');
--}}
<div
    x-data="{ show: false }"
    x-on:open-modal.window="if ($event.detail?.name === '{{ $name }}') show = true"
    x-on:close-modal.window="if (!$event.detail?.name || $event.detail.name === '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/50" x-on:click="show = false"></div>

    <div x-show="show" x-transition class="relative w-full {{ $widths[$maxWidth] ?? $widths['md'] }} max-h-[90vh] overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
        {{ $slot }}
    </div>
</div>
