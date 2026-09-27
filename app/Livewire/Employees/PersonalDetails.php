<?php

namespace App\Livewire\Employees;

use App\Models\Dependent;
use App\Models\Employee;
use App\Support\Hrm\DependentRelationship;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The "Personal Details" tile on My Profile — Build Plan Step 1.2.
 * Self-service only: contact-info editing and dependents/emergency-
 * contacts/insurance-beneficiary management for the SIGNED-IN user's
 * own Employee record. HR managing another employee's dependents is
 * real, later Phase 1 scope, not built here — "beneficiary personal
 * data" is Restricted-tier (docs/03-roles-and-permissions.md), and this
 * component only ever touches its own signed-in user's employee, never
 * an id passed in from outside.
 *
 * Every `#[Computed]` property here has a plain-method twin
 * (`resolveX()`) that action methods call instead of the magic
 * `$this->x` property Blade uses: Livewire's own Computed
 * implementation throws `CannotCallComputedDirectlyException` if you
 * call the attributed method directly, so `$this->x` (property access,
 * intercepted via `__get`) is the ONLY legal way to read it — but
 * Larastan doesn't understand that magic and flags it as an undefined
 * property when read from inside the class's own methods, not just
 * Blade. Splitting the actual logic into a plain resolver sidesteps the
 * false positive entirely instead of suppressing it.
 */
class PersonalDetails extends Component
{
    public string $phone = '';

    public string $personalEmail = '';

    public string $address = '';

    public ?int $editingDependentId = null;

    public string $firstName = '';

    public string $lastName = '';

    public string $relationship = '';

    public ?string $dateOfBirth = null;

    public ?string $passportNumber = null;

    public ?string $dependentPhone = null;

    public bool $isEmergencyContact = false;

    public bool $isInsuranceBeneficiary = false;

    public ?string $insuranceBeneficiaryPercentage = null;

    public function mount(): void
    {
        if ($employee = $this->resolveEmployee()) {
            $this->phone = $employee->phone ?? '';
            $this->personalEmail = $employee->personal_email ?? '';
            $this->address = $employee->address ?? '';
        }
    }

    #[Computed]
    public function employee(): ?Employee
    {
        return $this->resolveEmployee();
    }

    private function resolveEmployee(): ?Employee
    {
        return Auth::user()->employee;
    }

    #[Computed]
    public function dependents(): Collection
    {
        return $this->resolveDependents();
    }

    /**
     * @return Collection<int, Dependent>
     */
    private function resolveDependents(): Collection
    {
        return $this->resolveEmployee()?->dependents()->orderBy('first_name')->get() ?? new Collection;
    }

    /**
     * Total percentage already allocated to OTHER beneficiaries — i.e.
     * how much headroom is left for the one currently being added/edited.
     */
    #[Computed]
    public function otherBeneficiaryTotal(): float
    {
        return $this->resolveOtherBeneficiaryTotal();
    }

    private function resolveOtherBeneficiaryTotal(): float
    {
        return (float) $this->resolveDependents()
            ->where('is_insurance_beneficiary', true)
            ->reject(fn (Dependent $d) => $d->id === $this->editingDependentId)
            ->sum('insurance_beneficiary_percentage');
    }

    protected function contactRules(): array
    {
        return [
            'phone' => 'nullable|string|max:255',
            'personalEmail' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
        ];
    }

    public function saveContactInfo(): void
    {
        $employee = $this->resolveEmployee();

        if (! $employee) {
            return;
        }

        $validated = $this->validate($this->contactRules());

        $employee->update([
            'phone' => $validated['phone'] ?: null,
            'personal_email' => $validated['personalEmail'] ?: null,
            'address' => $validated['address'] ?: null,
        ]);

        session()->flash('contact-status', 'Contact information updated.');
    }

    protected function dependentRules(): array
    {
        return [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'relationship' => 'required|in:'.implode(',', array_column(DependentRelationship::cases(), 'value')),
            'dateOfBirth' => 'nullable|date',
            'passportNumber' => 'nullable|string|max:255',
            'dependentPhone' => 'nullable|string|max:255',
            'isEmergencyContact' => 'boolean',
            'isInsuranceBeneficiary' => 'boolean',
            'insuranceBeneficiaryPercentage' => 'nullable|numeric|min:0.01|max:100|required_if:isInsuranceBeneficiary,true',
        ];
    }

    public function editDependent(int $id): void
    {
        $dependent = $this->resolveEmployee()?->dependents()->findOrFail($id);

        if (! $dependent) {
            return;
        }

        $this->editingDependentId = $dependent->id;
        $this->firstName = $dependent->first_name;
        $this->lastName = $dependent->last_name;
        $this->relationship = $dependent->relationship->value;
        $this->dateOfBirth = $dependent->date_of_birth?->toDateString();
        $this->passportNumber = $dependent->passport_number;
        $this->dependentPhone = $dependent->phone;
        $this->isEmergencyContact = $dependent->is_emergency_contact;
        $this->isInsuranceBeneficiary = $dependent->is_insurance_beneficiary;
        $this->insuranceBeneficiaryPercentage = $dependent->insurance_beneficiary_percentage !== null
            ? (string) $dependent->insurance_beneficiary_percentage
            : null;
    }

    public function saveDependent(): void
    {
        $employee = $this->resolveEmployee();

        if (! $employee) {
            return;
        }

        $validated = $this->validate($this->dependentRules());

        if ($validated['isInsuranceBeneficiary']) {
            $requested = (float) $validated['insuranceBeneficiaryPercentage'];
            $otherTotal = $this->resolveOtherBeneficiaryTotal();

            if ($otherTotal + $requested > 100) {
                $remaining = 100 - $otherTotal;
                $this->addError(
                    'insuranceBeneficiaryPercentage',
                    "Beneficiary allocations can't exceed 100% in total — only {$remaining}% is unallocated."
                );

                return;
            }
        }

        $attributes = [
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'relationship' => $validated['relationship'],
            'date_of_birth' => $validated['dateOfBirth'],
            'passport_number' => $validated['passportNumber'],
            'phone' => $validated['dependentPhone'],
            'is_emergency_contact' => $validated['isEmergencyContact'],
            'is_insurance_beneficiary' => $validated['isInsuranceBeneficiary'],
            'insurance_beneficiary_percentage' => $validated['isInsuranceBeneficiary']
                ? $validated['insuranceBeneficiaryPercentage']
                : null,
        ];

        if ($this->editingDependentId) {
            $employee->dependents()->findOrFail($this->editingDependentId)->update($attributes);
        } else {
            $employee->dependents()->create($attributes);
        }

        unset($this->dependents);
        $this->resetDependentForm();
    }

    public function deleteDependent(int $id): void
    {
        $this->resolveEmployee()?->dependents()->findOrFail($id)->delete();

        unset($this->dependents);
    }

    public function cancelDependent(): void
    {
        $this->resetDependentForm();
        $this->resetErrorBag();
    }

    private function resetDependentForm(): void
    {
        $this->reset([
            'editingDependentId', 'firstName', 'lastName', 'relationship', 'dateOfBirth',
            'passportNumber', 'dependentPhone', 'isEmergencyContact', 'isInsuranceBeneficiary',
            'insuranceBeneficiaryPercentage',
        ]);
    }

    public function render()
    {
        return view('livewire.employees.personal-details');
    }
}
