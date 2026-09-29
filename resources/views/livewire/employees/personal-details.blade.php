<div class="space-y-6">
    @if (! $this->employee)
        <p class="text-sm text-slate-400">No employee record is linked to your account yet — contact HR.</p>
    @else
        <div>
            <h3 class="text-sm font-semibold text-slate-700">Contact information</h3>
            @if (session('contact-status'))
                <p class="mt-1 text-xs text-emerald-600">{{ session('contact-status') }}</p>
            @endif
            <form wire:submit="saveContactInfo" class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600">Phone</label>
                    <input type="text" wire:model="phone" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Personal email</label>
                    <input type="email" wire:model="personalEmail" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    @error('personalEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Address</label>
                    <input type="text" wire:model="address" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                </div>
                <div class="sm:col-span-3">
                    <x-button type="submit">Save contact info</x-button>
                </div>
            </form>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-700">Dependents & emergency contacts</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        Insurance beneficiary allocations across all dependents: <strong>{{ $this->otherBeneficiaryTotal + ($isInsuranceBeneficiary ? (float) ($insuranceBeneficiaryPercentage ?: 0) : 0) }}%</strong> allocated.
                    </p>
                </div>
                <x-button wire:click="openCreateDependent">Add dependent</x-button>
            </div>

            <ul class="mt-4 divide-y divide-slate-100">
                @forelse ($this->dependents as $dependent)
                    <li wire:key="{{ $dependent->id }}" class="flex items-center justify-between py-2.5 text-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-medium text-slate-900">{{ $dependent->fullName() }}</span>
                            <x-badge variant="neutral">{{ ucfirst($dependent->relationship->value) }}</x-badge>
                            @if ($dependent->is_emergency_contact)
                                <x-badge variant="warning">Emergency contact</x-badge>
                            @endif
                            @if ($dependent->is_insurance_beneficiary)
                                <x-badge variant="info">Beneficiary {{ $dependent->insurance_beneficiary_percentage }}%</x-badge>
                            @endif
                        </div>
                        <div class="flex gap-3 text-xs">
                            <button wire:click="editDependent({{ $dependent->id }})" class="font-semibold text-blue-600 hover:text-blue-500">Edit</button>
                            <button wire:click="deleteDependent({{ $dependent->id }})" wire:confirm="Remove this dependent?" class="font-semibold text-red-600 hover:text-red-500">Remove</button>
                        </div>
                    </li>
                @empty
                    <li class="py-2.5 text-sm text-slate-400">No dependents added yet.</li>
                @endforelse
            </ul>
        </div>

        <x-modal name="dependent-form" maxWidth="lg">
            <x-modal-header
                :title="$editingDependentId ? 'Edit Dependent' : 'Add New Dependent'"
                subtitle="Dependents, emergency contacts, and insurance beneficiaries."
            />
            <form wire:submit="saveDependent" class="space-y-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">First name</label>
                        <input type="text" wire:model="firstName" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                        @error('firstName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Last name</label>
                        <input type="text" wire:model="lastName" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                        @error('lastName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Relationship</label>
                        <select wire:model="relationship" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                            <option value="">— Select —</option>
                            @foreach (\App\Support\Hrm\DependentRelationship::cases() as $case)
                                <option value="{{ $case->value }}">{{ ucfirst($case->value) }}</option>
                            @endforeach
                        </select>
                        @error('relationship') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Date of birth</label>
                        <input type="date" wire:model="dateOfBirth" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Passport number</label>
                        <input type="text" wire:model="passportNumber" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Phone</label>
                        <input type="text" wire:model="dependentPhone" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    </div>
                </div>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-1.5 text-xs text-slate-600">
                        <input type="checkbox" wire:model="isEmergencyContact" class="rounded border-slate-300">
                        Emergency contact
                    </label>
                    <label class="flex items-center gap-1.5 text-xs text-slate-600">
                        <input type="checkbox" wire:model.live="isInsuranceBeneficiary" class="rounded border-slate-300">
                        Insurance beneficiary
                    </label>
                </div>
                @if ($isInsuranceBeneficiary)
                    <div class="max-w-[10rem]">
                        <label class="block text-xs font-medium text-slate-600">Beneficiary %</label>
                        <input type="number" step="0.01" wire:model="insuranceBeneficiaryPercentage" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                        @error('insuranceBeneficiaryPercentage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
                <div class="flex justify-end gap-2 pt-2">
                    <x-button type="button" variant="secondary" wire:click="cancelDependent">Cancel</x-button>
                    <x-button type="submit">{{ $editingDependentId ? 'Save changes' : 'Add dependent' }}</x-button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
