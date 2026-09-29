@props(['variant' => 'primary', 'tag' => 'button'])

@php
    $base = 'inline-flex items-center justify-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        // Every primary action in the reference is near-black, not blue
        // — "Sign In", "Edit Profile", "Add Dependent", "Request Leave"
        // all use this. See docs/build/DESIGN.md.
        'primary' => 'bg-slate-900 text-white hover:bg-slate-800',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
        'danger' => 'bg-red-600 text-white hover:bg-red-500',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-500',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($tag === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
