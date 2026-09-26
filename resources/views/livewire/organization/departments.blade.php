<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-base font-semibold text-slate-900">Departments</h2>
    <p class="mt-1 text-sm text-slate-500">Org units this tenant defines for itself — no code changes needed.</p>

    @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
        <form wire:submit="save" class="mt-4 grid grid-cols-1 gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-4">
            <div class="sm:col-span-2">
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
            <div class="sm:col-span-4 flex gap-2">
                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                    {{ $editingId ? 'Save changes' : 'Add department' }}
                </button>
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600">
                        Cancel
                    </button>
                @endif
            </div>
        </form>
    @endcan

    <ul class="mt-4 divide-y divide-slate-100">
        @forelse ($this->departments as $department)
            <li class="flex items-center justify-between py-2.5 text-sm">
                <div>
                    <span class="font-medium text-slate-900">{{ $department->name }}</span>
                    @if ($department->code)
                        <span class="ml-2 text-xs text-slate-400">{{ $department->code }}</span>
                    @endif
                    @if ($department->parent)
                        <span class="ml-2 text-xs text-slate-400">under {{ $department->parent->name }}</span>
                    @endif
                </div>
                @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
                    <div class="flex gap-3 text-xs">
                        <button wire:click="edit({{ $department->id }})" class="font-semibold text-indigo-600 hover:text-indigo-500">Edit</button>
                        <button wire:click="delete({{ $department->id }})" wire:confirm="Remove this department?" class="font-semibold text-red-600 hover:text-red-500">Remove</button>
                    </div>
                @endcan
            </li>
        @empty
            <li class="py-2.5 text-sm text-slate-400">No departments yet.</li>
        @endforelse
    </ul>
</div>
