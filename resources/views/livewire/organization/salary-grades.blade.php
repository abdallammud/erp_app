<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Salary Grades</h2>
            <p class="mt-1 text-sm text-slate-500">Tenant-defined pay bands — a Contract picks one of these. See docs/04-module-hrm.md §C.</p>
        </div>
        @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
            <x-button wire:click="openCreate">Add salary grade</x-button>
        @endcan
    </div>

    <ul class="mt-4 divide-y divide-slate-100">
        @forelse ($this->salaryGrades as $grade)
            <li wire:key="{{ $grade->id }}" class="flex items-center justify-between py-2.5 text-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-medium text-slate-900">{{ $grade->name }}</span>
                    <x-badge variant="neutral">{{ $grade->code }}</x-badge>
                    <span class="text-xs text-slate-400">
                        {{ number_format($grade->min_salary, 2) }} – {{ number_format($grade->max_salary, 2) }} {{ $grade->currency }}
                    </span>
                </div>
                @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
                    <div class="flex gap-3 text-xs">
                        <button wire:click="edit({{ $grade->id }})" class="font-semibold text-blue-600 hover:text-blue-500">Edit</button>
                        <button wire:click="delete({{ $grade->id }})" wire:confirm="Remove this salary grade?" class="font-semibold text-red-600 hover:text-red-500">Remove</button>
                    </div>
                @endcan
            </li>
        @empty
            <li class="py-2.5 text-sm text-slate-400">No salary grades yet.</li>
        @endforelse
    </ul>

    @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
        <x-modal name="salary-grade-form">
            <x-modal-header
                :title="$editingId ? 'Edit Salary Grade' : 'Add Salary Grade'"
                subtitle="A Contract picks one of these as its pay band."
            />
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
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
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Min salary</label>
                        <input type="number" step="0.01" wire:model="minSalary" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                        @error('minSalary') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Max salary</label>
                        <input type="number" step="0.01" wire:model="maxSalary" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                        @error('maxSalary') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Currency</label>
                        <input type="text" wire:model="currency" maxlength="3" class="mt-1 block w-full rounded-md border-slate-300 text-sm uppercase">
                        @error('currency') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <x-button type="button" variant="secondary" wire:click="cancel">Cancel</x-button>
                    <x-button type="submit">{{ $editingId ? 'Save changes' : 'Add salary grade' }}</x-button>
                </div>
            </form>
        </x-modal>
    @endcan
</div>
