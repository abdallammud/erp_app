<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Departments</h2>
            <p class="mt-1 text-sm text-slate-500">Org units this tenant defines for itself — no code changes needed.</p>
        </div>
        @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
            <x-button wire:click="openCreate">Add department</x-button>
        @endcan
    </div>

    <ul class="mt-4 divide-y divide-slate-100">
        @forelse ($this->departments as $department)
            <li wire:key="{{ $department->id }}" class="flex items-center justify-between py-2.5 text-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-medium text-slate-900">{{ $department->name }}</span>
                    @if ($department->code)
                        <x-badge variant="neutral">{{ $department->code }}</x-badge>
                    @endif
                    @if ($department->parent)
                        <span class="text-xs text-slate-400">under {{ $department->parent->name }}</span>
                    @endif
                </div>
                @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
                    <div class="flex gap-3 text-xs">
                        <button wire:click="edit({{ $department->id }})" class="font-semibold text-blue-600 hover:text-blue-500">Edit</button>
                        <button wire:click="delete({{ $department->id }})" wire:confirm="Remove this department?" class="font-semibold text-red-600 hover:text-red-500">Remove</button>
                    </div>
                @endcan
            </li>
        @empty
            <li class="py-2.5 text-sm text-slate-400">No departments yet.</li>
        @endforelse
    </ul>

    @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
        <x-modal name="department-form">
            <x-modal-header
                :title="$editingId ? 'Edit Department' : 'Add Department'"
                subtitle="Org units this tenant defines for itself."
            />
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600">Name</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Code</label>
                    <input type="text" wire:model="code" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Parent department</label>
                    <select wire:model="parentDepartmentId" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                        <option value="">— None —</option>
                        @foreach ($this->departments as $department)
                            @if ($department->id !== $editingId)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('parentDepartmentId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <x-button type="button" variant="secondary" wire:click="cancel">Cancel</x-button>
                    <x-button type="submit">{{ $editingId ? 'Save changes' : 'Add department' }}</x-button>
                </div>
            </form>
        </x-modal>
    @endcan
</div>
