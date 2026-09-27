@props(['variant' => 'neutral'])

@php
    $variants = [
        'success' => 'bg-emerald-50 text-emerald-700',
        'warning' => 'bg-amber-50 text-amber-700',
        'info' => 'bg-blue-50 text-blue-700',
        'danger' => 'bg-red-50 text-red-700',
        'purple' => 'bg-purple-50 text-purple-700',
        'neutral' => 'bg-slate-100 text-slate-600',
    ];

    $classes = 'inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold '.($variants[$variant] ?? $variants['neutral']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
