<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Duty Stations</h2>
            <p class="mt-1 text-sm text-slate-500">Offices/locations this tenant operates from.</p>
        </div>
        @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
            <x-button wire:click="openCreate">Add duty station</x-button>
        @endcan
    </div>

    <ul class="mt-4 divide-y divide-slate-100">
        @forelse ($this->dutyStations as $station)
            <li wire:key="{{ $station->id }}" class="flex items-center justify-between py-2.5 text-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-medium text-slate-900">{{ $station->name }}</span>
                    <x-badge variant="neutral">{{ $station->country_code }}@if($station->city), {{ $station->city }}@endif</x-badge>
                </div>
                @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
                    <div class="flex gap-3 text-xs">
                        <button wire:click="edit({{ $station->id }})" class="font-semibold text-blue-600 hover:text-blue-500">Edit</button>
                        <button wire:click="delete({{ $station->id }})" wire:confirm="Remove this duty station?" class="font-semibold text-red-600 hover:text-red-500">Remove</button>
                    </div>
                @endcan
            </li>
        @empty
            <li class="py-2.5 text-sm text-slate-400">No duty stations yet.</li>
        @endforelse
    </ul>

    @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
        <x-modal name="duty-station-form">
            <x-modal-header
                :title="$editingId ? 'Edit Duty Station' : 'Add Duty Station'"
                subtitle="Offices/locations this tenant operates from."
            />
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600">Name</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Country code</label>
                        <input type="text" wire:model="countryCode" maxlength="2" placeholder="SO" class="mt-1 block w-full rounded-md border-slate-300 text-sm uppercase">
                        @error('countryCode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">City</label>
                        <input type="text" wire:model="city" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Code</label>
                    <input type="text" wire:model="code" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Address</label>
                    <input type="text" wire:model="address" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <x-button type="button" variant="secondary" wire:click="cancel">Cancel</x-button>
                    <x-button type="submit">{{ $editingId ? 'Save changes' : 'Add duty station' }}</x-button>
                </div>
            </form>
        </x-modal>
    @endcan
</div>
