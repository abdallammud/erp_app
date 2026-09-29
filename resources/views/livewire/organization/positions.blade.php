<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Positions</h2>
            <p class="mt-1 text-sm text-slate-500">Job title/grade slots this tenant defines for itself.</p>
        </div>
        @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
            <x-button wire:click="openCreate">Add position</x-button>
        @endcan
    </div>

    <ul class="mt-4 divide-y divide-slate-100">
        @forelse ($this->positions as $position)
            <li wire:key="{{ $position->id }}" class="flex items-center justify-between py-2.5 text-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-medium text-slate-900">{{ $position->title }}</span>
                    @if ($position->grade)
                        <x-badge variant="neutral">{{ $position->grade }}</x-badge>
                    @endif
                    @if ($position->department)
                        <span class="text-xs text-slate-400">{{ $position->department->name }}</span>
                    @endif
                </div>
                @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
                    <div class="flex gap-3 text-xs">
                        <button wire:click="edit({{ $position->id }})" class="font-semibold text-blue-600 hover:text-blue-500">Edit</button>
                        <button wire:click="delete({{ $position->id }})" wire:confirm="Remove this position?" class="font-semibold text-red-600 hover:text-red-500">Remove</button>
                    </div>
                @endcan
            </li>
        @empty
            <li class="py-2.5 text-sm text-slate-400">No positions yet.</li>
        @endforelse
    </ul>

    @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
        <x-modal name="position-form">
            <x-modal-header
                :title="$editingId ? 'Edit Position' : 'Add Position'"
                subtitle="Job title/grade slots this tenant defines for itself."
            />
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600">Title</label>
                    <input type="text" wire:model="title" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Grade</label>
                        <input type="text" wire:model="grade" placeholder="P3" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Department</label>
                        <select wire:model="departmentId" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                            <option value="">— None —</option>
                            @foreach ($this->departmentOptions as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <x-button type="button" variant="secondary" wire:click="cancel">Cancel</x-button>
                    <x-button type="submit">{{ $editingId ? 'Save changes' : 'Add position' }}</x-button>
                </div>
            </form>
        </x-modal>
    @endcan
</div>
