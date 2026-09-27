<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-base font-semibold text-slate-900">Salary Grades</h2>
    <p class="mt-1 text-sm text-slate-500">Tenant-defined pay bands — a Contract picks one of these. See docs/04-module-hrm.md §C.</p>

    @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
        <form wire:submit="save" class="mt-4 grid grid-cols-1 gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-6">
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
            <div class="sm:col-span-6 flex gap-2">
                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                    {{ $editingId ? 'Save changes' : 'Add salary grade' }}
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
        @forelse ($this->salaryGrades as $grade)
            <li class="flex items-center justify-between py-2.5 text-sm">
                <div>
                    <span class="font-medium text-slate-900">{{ $grade->name }}</span>
                    <span class="ml-2 text-xs text-slate-400">{{ $grade->code }}</span>
                    <span class="ml-2 text-xs text-slate-400">
                        {{ number_format($grade->min_salary, 2) }} – {{ number_format($grade->max_salary, 2) }} {{ $grade->currency }}
                    </span>
                </div>
                @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
                    <div class="flex gap-3 text-xs">
                        <button wire:click="edit({{ $grade->id }})" class="font-semibold text-indigo-600 hover:text-indigo-500">Edit</button>
                        <button wire:click="delete({{ $grade->id }})" wire:confirm="Remove this salary grade?" class="font-semibold text-red-600 hover:text-red-500">Remove</button>
                    </div>
                @endcan
            </li>
        @empty
            <li class="py-2.5 text-sm text-slate-400">No salary grades yet.</li>
        @endforelse
    </ul>
</div>
