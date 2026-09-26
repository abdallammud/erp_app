<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-slate-500">Every create, update, and delete on a tenant-scoped record, newest first.</p>

        <select wire:model.live="event" class="rounded-md border-slate-300 text-xs">
            <option value="">All events</option>
            <option value="created">Created</option>
            <option value="updated">Updated</option>
            <option value="deleted">Deleted</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        @forelse ($this->entries as $entry)
            <div wire:key="{{ $entry->id }}" class="border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <span @class([
                            'rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide',
                            'bg-emerald-50 text-emerald-700' => $entry->event === 'created',
                            'bg-indigo-50 text-indigo-700' => $entry->event === 'updated',
                            'bg-red-50 text-red-700' => $entry->event === 'deleted',
                        ])>
                            {{ $entry->event }}
                        </span>
                        <span class="text-sm font-medium text-slate-900">
                            {{ class_basename($entry->subject_type) }} #{{ $entry->subject_id }}
                        </span>
                        <span class="text-xs text-slate-400">by {{ $entry->causer?->name ?? 'System' }}</span>
                    </div>
                    <span class="text-xs text-slate-400">{{ $entry->created_at->diffForHumans() }}</span>
                </div>

                @php
                    $new = $entry->attribute_changes?->get('attributes', []) ?? [];
                    $old = $entry->attribute_changes?->get('old', []) ?? [];
                    $fields = collect(array_keys($new))->merge(array_keys($old))->unique();
                @endphp

                @if ($fields->isNotEmpty())
                    <details class="mt-2">
                        <summary class="cursor-pointer text-xs font-medium text-indigo-600 hover:text-indigo-500">
                            {{ $fields->count() }} field{{ $fields->count() === 1 ? '' : 's' }} changed
                        </summary>
                        <table class="mt-2 w-full text-xs">
                            <tbody>
                                @foreach ($fields as $field)
                                    <tr class="border-t border-slate-50">
                                        <td class="py-1 pr-3 font-medium text-slate-500">{{ $field }}</td>
                                        @if (array_key_exists($field, $old) && array_key_exists($field, $new))
                                            <td class="py-1 pr-2 text-slate-400 line-through">{{ $this->formatValue($old[$field]) }}</td>
                                            <td class="py-1 text-slate-900">{{ $this->formatValue($new[$field]) }}</td>
                                        @elseif (array_key_exists($field, $old))
                                            <td class="py-1 pr-2 text-slate-400 line-through" colspan="2">{{ $this->formatValue($old[$field]) }}</td>
                                        @else
                                            <td class="py-1 text-slate-900" colspan="2">{{ $this->formatValue($new[$field]) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </details>
                @endif
            </div>
        @empty
            <p class="px-5 py-10 text-center text-sm text-slate-400">No activity recorded yet.</p>
        @endforelse
    </div>

    <div>
        {{ $this->entries->links() }}
    </div>
</div>
