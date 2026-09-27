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
                    <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                        Save contact info
                    </button>
                </div>
            </form>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <h3 class="text-sm font-semibold text-slate-700">Dependents & emergency contacts</h3>
            <p class="mt-1 text-xs text-slate-500">
                Insurance beneficiary allocations across all dependents: <strong>{{ $this->otherBeneficiaryTotal + ($isInsuranceBeneficiary ? (float) ($insuranceBeneficiaryPercentage ?: 0) : 0) }}%</strong> allocated.
            </p>

            <form wire:submit="saveDependent" class="mt-3 grid grid-cols-1 gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-4">
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
                <div class="flex items-end gap-2">
                    <label class="flex items-center gap-1.5 text-xs text-slate-600">
                        <input type="checkbox" wire:model="isEmergencyContact" class="rounded border-slate-300">
                        Emergency contact
                    </label>
                </div>
                <div class="flex items-end gap-2">
                    <label class="flex items-center gap-1.5 text-xs text-slate-600">
                        <input type="checkbox" wire:model.live="isInsuranceBeneficiary" class="rounded border-slate-300">
                        Insurance beneficiary
                    </label>
                </div>
                @if ($isInsuranceBeneficiary)
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Beneficiary %</label>
                        <input type="number" step="0.01" wire:model="insuranceBeneficiaryPercentage" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                        @error('insuranceBeneficiaryPercentage') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
                <div class="sm:col-span-4 flex gap-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                        {{ $editingDependentId ? 'Save changes' : 'Add dependent' }}
                    </button>
                    @if ($editingDependentId)
                        <button type="button" wire:click="cancelDependent" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600">
                            Cancel
                        </button>
                    @endif
                </div>
            </form>

            <ul class="mt-4 divide-y divide-slate-100">
                @forelse ($this->dependents as $dependent)
                    <li class="flex items-center justify-between py-2.5 text-sm">
                        <div>
                            <span class="font-medium text-slate-900">{{ $dependent->fullName() }}</span>
                            <span class="ml-2 text-xs text-slate-400">{{ ucfirst($dependent->relationship->value) }}</span>
                            @if ($dependent->is_emergency_contact)
                                <span class="ml-2 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Emergency contact</span>
                            @endif
                            @if ($dependent->is_insurance_beneficiary)
                                <span class="ml-2 rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">
                                    Beneficiary {{ $dependent->insurance_beneficiary_percentage }}%
                                </span>
                            @endif
                        </div>
                        <div class="flex gap-3 text-xs">
                            <button wire:click="editDependent({{ $dependent->id }})" class="font-semibold text-indigo-600 hover:text-indigo-500">Edit</button>
                            <button wire:click="deleteDependent({{ $dependent->id }})" wire:confirm="Remove this dependent?" class="font-semibold text-red-600 hover:text-red-500">Remove</button>
                        </div>
                    </li>
                @empty
                    <li class="py-2.5 text-sm text-slate-400">No dependents added yet.</li>
                @endforelse
            </ul>
        </div>
    @endif
</div>
