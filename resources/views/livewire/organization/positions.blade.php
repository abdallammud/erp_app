<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-base font-semibold text-slate-900">Positions</h2>
    <p class="mt-1 text-sm text-slate-500">Job title/grade slots this tenant defines for itself.</p>

    @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
        <form wire:submit="save" class="mt-4 grid grid-cols-1 gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-slate-600">Title</label>
                <input type="text" wire:model="title" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
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
            <div class="sm:col-span-4 flex gap-2">
                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                    {{ $editingId ? 'Save changes' : 'Add position' }}
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
        @forelse ($this->positions as $position)
            <li class="flex items-center justify-between py-2.5 text-sm">
                <div>
                    <span class="font-medium text-slate-900">{{ $position->title }}</span>
                    @if ($position->grade)
                        <span class="ml-2 text-xs text-slate-400">{{ $position->grade }}</span>
                    @endif
                    @if ($position->department)
                        <span class="ml-2 text-xs text-slate-400">{{ $position->department->name }}</span>
                    @endif
                </div>
                @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
                    <div class="flex gap-3 text-xs">
                        <button wire:click="edit({{ $position->id }})" class="font-semibold text-indigo-600 hover:text-indigo-500">Edit</button>
                        <button wire:click="delete({{ $position->id }})" wire:confirm="Remove this position?" class="font-semibold text-red-600 hover:text-red-500">Remove</button>
                    </div>
                @endcan
            </li>
        @empty
            <li class="py-2.5 text-sm text-slate-400">No positions yet.</li>
        @endforelse
    </ul>
</div>
