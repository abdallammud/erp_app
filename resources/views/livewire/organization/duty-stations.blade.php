<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="text-base font-semibold text-slate-900">Duty Stations</h2>
    <p class="mt-1 text-sm text-slate-500">Offices/locations this tenant operates from.</p>

    @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
        <form wire:submit="save" class="mt-4 grid grid-cols-1 gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-slate-600">Name</label>
                <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600">Country code</label>
                <input type="text" wire:model="countryCode" maxlength="2" placeholder="SO" class="mt-1 block w-full rounded-md border-slate-300 text-sm uppercase">
                @error('countryCode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600">City</label>
                <input type="text" wire:model="city" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-slate-600">Code</label>
                <input type="text" wire:model="code" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-slate-600">Address</label>
                <input type="text" wire:model="address" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
            </div>
            <div class="sm:col-span-4 flex gap-2">
                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                    {{ $editingId ? 'Save changes' : 'Add duty station' }}
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
        @forelse ($this->dutyStations as $station)
            <li class="flex items-center justify-between py-2.5 text-sm">
                <div>
                    <span class="font-medium text-slate-900">{{ $station->name }}</span>
                    <span class="ml-2 text-xs text-slate-400">{{ $station->country_code }}@if($station->city), {{ $station->city }}@endif</span>
                </div>
                @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
                    <div class="flex gap-3 text-xs">
                        <button wire:click="edit({{ $station->id }})" class="font-semibold text-indigo-600 hover:text-indigo-500">Edit</button>
                        <button wire:click="delete({{ $station->id }})" wire:confirm="Remove this duty station?" class="font-semibold text-red-600 hover:text-red-500">Remove</button>
                    </div>
                @endcan
            </li>
        @empty
            <li class="py-2.5 text-sm text-slate-400">No duty stations yet.</li>
        @endforelse
    </ul>
</div>
