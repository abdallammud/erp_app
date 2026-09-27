@props(['label', 'value', 'subtext' => null, 'icon' => null, 'color' => 'slate'])

@php
    $colors = [
        'blue' => 'text-blue-600',
        'green' => 'text-emerald-600',
        'purple' => 'text-purple-600',
        'orange' => 'text-orange-600',
        'red' => 'text-red-600',
        'slate' => 'text-slate-900',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white p-4']) }}>
    <div class="flex items-center gap-1.5 text-xs text-slate-500">
        @if ($icon)
            <x-icon :name="$icon" class="h-3.5 w-3.5" />
        @endif
        <span>{{ $label }}</span>
    </div>
    <p class="mt-1.5 text-2xl font-bold {{ $colors[$color] ?? $colors['slate'] }}">{{ $value }}</p>
    @if ($subtext)
        <p class="mt-0.5 text-xs text-slate-400">{{ $subtext }}</p>
    @endif
</div>
