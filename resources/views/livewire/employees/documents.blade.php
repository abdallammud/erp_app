<div class="space-y-4">
    @if (! $this->employee)
        <p class="text-sm text-slate-400">No employee record is linked to your account yet — contact HR.</p>
    @else
        <form wire:submit="upload" class="grid grid-cols-1 gap-3 sm:grid-cols-4 sm:items-end">
            <div class="sm:col-span-1">
                <label class="block text-xs font-medium text-slate-600">Category</label>
                <input type="text" wire:model="category" placeholder="e.g. ID, Contract"
                       class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                @error('category') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-1">
                <label class="block text-xs font-medium text-slate-600">Expiry date (optional)</label>
                <input type="date" wire:model="expiryDate" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                @error('expiryDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-1">
                <label class="block text-xs font-medium text-slate-600">File</label>
                <input type="file" wire:model="file" class="mt-1 block w-full text-xs">
                @error('file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-1">
                <button type="submit" wire:loading.attr="disabled" wire:target="upload,file"
                        class="w-full rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                    <span wire:loading.remove wire:target="upload">Upload</span>
                    <span wire:loading wire:target="upload">Uploading…</span>
                </button>
            </div>
        </form>

        <div class="divide-y divide-slate-100 rounded-lg border border-slate-200">
            @forelse ($this->documents as $document)
                <div wire:key="{{ $document->id }}" class="flex items-center justify-between gap-3 px-4 py-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $document->original_filename }}</p>
                        <p class="text-xs text-slate-400">
                            {{ $document->category }}
                            @if ($document->expiry_date)
                                &middot; expires {{ $document->expiry_date->toFormattedDateString() }}
                            @endif
                            &middot;
                            @if ($document->is_verified)
                                <span class="text-emerald-600">Verified</span>
                            @else
                                <span class="text-amber-600">Pending verification</span>
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('documents.download', $document) }}"
                       class="shrink-0 text-xs font-semibold text-indigo-600 hover:text-indigo-500">
                        Download
                    </a>
                </div>
            @empty
                <p class="px-4 py-6 text-center text-sm text-slate-400">No documents uploaded yet.</p>
            @endforelse
        </div>
    @endif
</div>
