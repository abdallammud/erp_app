@props(['name', 'maxWidth' => 'md'])

@php
    $widths = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl'];
@endphp

{{--
    Trigger from anywhere with a plain DOM event (no Alpine scope
    needed on the trigger itself):
    onclick="window.dispatchEvent(new CustomEvent('open-modal', {detail: '{{ $name }}'}))"

    Close from a Livewire action after a successful save by dispatching
    a browser event: $this->dispatch('close-modal', name: '{{ $name }}');
--}}
<div
    x-data="{ show: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') show = true"
    x-on:close-modal.window="if (!$event.detail || $event.detail === '{{ $name }}') show = false"
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
