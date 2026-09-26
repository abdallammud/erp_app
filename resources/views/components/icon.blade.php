@props(['name'])

{{-- No default `class` in this merge — every call site passes its own
     size (h-5, h-[18px], etc.); merging a default here would apply
     BOTH sizes at once since Tailwind utility classes don't override
     each other by source order. --}}
<svg {{ $attributes->merge(['fill' => 'none', 'viewBox' => '0 0 24 24', 'stroke' => 'currentColor', 'stroke-width' => '1.75']) }}>
    @switch($name)
        @case('home')
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5" />
            @break
        @case('user')
            <circle cx="12" cy="8" r="3.5" />
            <path stroke-linecap="round" d="M4.5 20c0-4 3.5-6.5 7.5-6.5s7.5 2.5 7.5 6.5" />
            @break
        @case('users')
            <circle cx="9" cy="8" r="3" />
            <circle cx="16.5" cy="9.5" r="2.25" />
            <path stroke-linecap="round" d="M3.5 20c0-3.5 2.5-5.5 5.5-5.5s5.5 2 5.5 5.5" />
            <path stroke-linecap="round" d="M15 15c2.5 0 4.5 1.5 5 4.5" />
            @break
        @case('briefcase')
            <rect x="3" y="8" width="18" height="11" rx="1.5" />
            <path d="M9 8V6.5A1.5 1.5 0 0 1 10.5 5h3A1.5 1.5 0 0 1 15 6.5V8" />
            <path stroke-linecap="round" d="M3 13h18" />
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="1.5" />
            <path stroke-linecap="round" d="M3 9.5h18" />
            <path stroke-linecap="round" d="M8 3v4M16 3v4" />
            @break
        @case('chart-bar')
            <rect x="4" y="12" width="3.2" height="8" />
            <rect x="10.4" y="6" width="3.2" height="14" />
            <rect x="16.8" y="14.5" width="3.2" height="5.5" />
            @break
        @case('clipboard-check')
            <rect x="5" y="4.5" width="14" height="16.5" rx="1.5" />
            <rect x="9" y="3" width="6" height="3" rx="1" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.5 13 2.25 2.25L15.5 10" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3.5 2" />
            @break
        @case('book-open')
            <path stroke-linejoin="round" d="M12 6 4 4.5v13L12 19l8-1.5v-13z" />
            <path d="M12 6v13" />
            @break
        @case('cube')
            <path stroke-linejoin="round" d="M12 3 20 7.5v9L12 21 4 16.5v-9z" />
            <path stroke-linejoin="round" d="M4 7.5 12 12l8-4.5" />
            <path d="M12 12v9" />
            @break
        @case('shield-check')
            <path stroke-linejoin="round" d="M12 3 19 6v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4.5" />
            @break
        @case('bell')
            <path stroke-linejoin="round" d="M6 9a6 6 0 0 1 12 0v5l2 3H4l2-3z" />
            <path stroke-linecap="round" d="M10 19a2 2 0 0 0 4 0" />
            @break
        @case('building')
            <rect x="4" y="3" width="10" height="18" />
            <rect x="14" y="9" width="6" height="12" />
            <rect x="8" y="16" width="2" height="5" />
            @break
        @case('history')
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5v4h4" />
            @break
        @case('currency-dollar')
            <circle cx="12" cy="12" r="9" />
            <text x="12" y="16.3" text-anchor="middle" font-size="10.5" font-weight="600" stroke="none" fill="currentColor">$</text>
            @break
        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
