<?php

namespace App\Livewire\Organization;

use App\Models\DutyStation;
use App\Support\Authorization\Permission;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Simple CRUD for the tenant's duty stations (offices/locations) — Build
 * Plan Step 0.5. Anyone with `hrm.org.view` sees this; only
 * `hrm.org.edit` can mutate.
 */
class DutyStations extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public ?string $code = null;

    public string $countryCode = '';

    public ?string $city = null;

    public ?string $address = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'countryCode' => 'required|string|size:2',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
        ];
    }

    #[Computed]
    public function dutyStations(): Collection
    {
        return DutyStation::orderBy('name')->get();
    }

    public function edit(int $id): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        $station = DutyStation::findOrFail($id);

        $this->editingId = $station->id;
        $this->name = $station->name;
        $this->code = $station->code;
        $this->countryCode = $station->country_code;
        $this->city = $station->city;
        $this->address = $station->address;
    }

    public function save(): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        $validated = $this->validate();
        $validated['countryCode'] = strtoupper($validated['countryCode']);
        $validated['country_code'] = $validated['countryCode'];
        unset($validated['countryCode']);

        if ($this->editingId) {
            DutyStation::findOrFail($this->editingId)->update($validated);
        } else {
            DutyStation::create($validated);
        }

        unset($this->dutyStations);
        $this->reset(['editingId', 'name', 'code', 'countryCode', 'city', 'address']);
    }

    public function delete(int $id): void
    {
        $this->authorize(Permission::HrmOrgEdit->value);

        DutyStation::findOrFail($id)->delete();

        unset($this->dutyStations);
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'code', 'countryCode', 'city', 'address']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.organization.duty-stations');
    }
}
