<div>
    <p class="text-xs text-slate-500">Every change to your profile and employee record, newest first, with who made it.</p>

    <ul class="mt-3 divide-y divide-slate-100">
        @forelse ($this->entries as $entry)
            <li wire:key="{{ $entry->id }}" class="py-3 text-sm">
                <div class="flex items-center justify-between">
                    <span>
                        <span @class([
                            'rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide',
                            'bg-emerald-50 text-emerald-700' => $entry->event === 'created',
                            'bg-blue-50 text-blue-700' => $entry->event === 'updated',
                            'bg-red-50 text-red-700' => $entry->event === 'deleted',
                        ])>
                            {{ $entry->event }}
                        </span>
                        <span class="ml-2 font-medium text-slate-900">{{ class_basename($entry->subject_type) }}</span>
                    </span>
                    <span class="text-xs text-slate-400">{{ $entry->created_at->diffForHumans() }} by {{ $entry->causer?->name ?? 'System' }}</span>
                </div>

                @php
                    $new = $entry->attribute_changes?->get('attributes', []) ?? [];
                    $old = $entry->attribute_changes?->get('old', []) ?? [];
                    $fields = collect(array_keys($new))->merge(array_keys($old))->unique();
                @endphp

                @if ($fields->isNotEmpty())
                    <ul class="mt-1.5 space-y-0.5 pl-1 text-xs text-slate-500">
                        @foreach ($fields as $field)
                            <li>
                                <span class="font-medium text-slate-600">{{ $field }}:</span>
                                @if (array_key_exists($field, $old) && array_key_exists($field, $new))
                                    <span class="text-slate-400 line-through">{{ $this->formatValue($old[$field]) }}</span>
                                    → {{ $this->formatValue($new[$field]) }}
                                @else
                                    {{ $this->formatValue($new[$field] ?? $old[$field] ?? null) }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @empty
            <li class="py-6 text-center text-sm text-slate-400">No history yet.</li>
        @endforelse
    </ul>
</div>
