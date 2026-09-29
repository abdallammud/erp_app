<div class="space-y-6">
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-3">
            <h2 class="text-base font-semibold text-slate-900">Employees</h2>
            @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
                <x-button wire:click="openCreate">Add employee</x-button>
            @endcan
        </div>
        @forelse ($this->employees as $employee)
            @php($contract = $employee->currentContract())
            <div wire:key="{{ $employee->id }}" class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4 last:border-b-0">
                <div>
                    <p class="text-sm font-semibold text-slate-900">
                        {{ $employee->fullName() }}
                        <span class="ml-1 text-xs font-normal text-slate-400">{{ $employee->employee_number }}</span>
                    </p>
                    <p class="mt-0.5 text-xs text-slate-500">
                        {{ $employee->position?->title ?? 'No position' }}
                        @if ($employee->department) &middot; {{ $employee->department->name }} @endif
                        @if ($employee->dutyStation) &middot; {{ $employee->dutyStation->name }} @endif
                    </p>
                    @if ($contract)
                        <p class="mt-0.5 text-xs text-slate-400">
                            {{ ucwords(str_replace('_', ' ', $contract->type->value)) }} contract
                            &middot; {{ $contract->salaryGrade->name }}
                            &middot; from {{ $contract->start_date->toFormattedDateString() }}
                            @if ($contract->end_date) to {{ $contract->end_date->toFormattedDateString() }} @endif
                        </p>
                    @endif
                </div>
                @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
                    <button wire:click="edit({{ $employee->id }})" class="text-xs font-semibold text-blue-600 hover:text-blue-500">
                        Edit
                    </button>
                @endcan
            </div>
        @empty
            <p class="px-5 py-10 text-center text-sm text-slate-400">No employees yet.</p>
        @endforelse
    </div>

    @can(\App\Support\Authorization\Permission::HrmOrgEdit->value)
        <x-modal name="employee-form" maxWidth="2xl">
            <x-modal-header
                :title="$editingId ? 'Edit Employee' : 'Add Employee'"
                subtitle="Every employee has a contract and a salary grade from the moment they're created."
            />
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Employee number</label>
                        <input type="text" wire:model="employeeNumber" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                        @error('employeeNumber') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
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
                        <label class="block text-xs font-medium text-slate-600">Hire date</label>
                        <input type="date" wire:model="hireDate" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                        @error('hireDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Gender</label>
                        <select wire:model="gender" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                            <option value="">— Not specified —</option>
                            @foreach (\App\Support\Hrm\Gender::cases() as $case)
                                <option value="{{ $case->value }}">{{ ucwords(str_replace('_', ' ', $case->value)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Date of birth</label>
                        <input type="date" wire:model="dateOfBirth" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">National ID</label>
                        <input type="text" wire:model="nationalId" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Phone</label>
                        <input type="text" wire:model="phone" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Personal email</label>
                        <input type="email" wire:model="personalEmail" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                        @error('personalEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Address</label>
                        <input type="text" wire:model="address" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Department</label>
                        <select wire:model="departmentId" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                            <option value="">— None —</option>
                            @foreach ($this->departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Position</label>
                        <select wire:model="positionId" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                            <option value="">— None —</option>
                            @foreach ($this->positions as $position)
                                <option value="{{ $position->id }}">{{ $position->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Duty station</label>
                        <select wire:model="dutyStationId" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                            <option value="">— None —</option>
                            @foreach ($this->dutyStations as $station)
                                <option value="{{ $station->id }}">{{ $station->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Reports to</label>
                        <select wire:model="reportsToId" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                            <option value="">— None —</option>
                            @foreach ($this->employees as $manager)
                                @if ($manager->id !== $editingId)
                                    <option value="{{ $manager->id }}">{{ $manager->fullName() }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Staff category</label>
                        <select wire:model="staffCategory" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                            <option value="">— Select —</option>
                            @foreach (\App\Support\Hrm\StaffCategory::cases() as $case)
                                <option value="{{ $case->value }}">{{ ucwords(str_replace('_', ' ', $case->value)) }}</option>
                            @endforeach
                        </select>
                        @error('staffCategory') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600">Portal account (optional)</label>
                        <select wire:model="userId" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                            <option value="">— No login access —</option>
                            @foreach ($this->linkableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @unless ($editingId)
                    <div class="rounded-lg bg-slate-50 p-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">First contract</h3>
                        <p class="mt-1 text-xs text-slate-400">Every employee needs one to exist — see docs/build/00-build-plan.md Step 1.1.</p>
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Contract type</label>
                                <select wire:model="contractType" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                                    <option value="">— Select —</option>
                                    @foreach (\App\Support\Hrm\ContractType::cases() as $case)
                                        <option value="{{ $case->value }}">{{ ucwords(str_replace('_', ' ', $case->value)) }}</option>
                                    @endforeach
                                </select>
                                @error('contractType') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Salary grade</label>
                                <select wire:model="salaryGradeId" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                                    <option value="">— Select —</option>
                                    @foreach ($this->salaryGrades as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->name }} ({{ $grade->code }})</option>
                                    @endforeach
                                </select>
                                @error('salaryGradeId')
                                    <p class="mt-1 text-xs text-red-600">
                                        {{ $this->salaryGrades->isEmpty() ? 'No salary grades exist yet — add one under Organization first.' : $message }}
                                    </p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">Start date</label>
                                <input type="date" wire:model="contractStartDate" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                                @error('contractStartDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-600">End date (optional)</label>
                                <input type="date" wire:model="contractEndDate" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                                @error('contractEndDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                @endunless

                <div class="flex justify-end gap-2 pt-2">
                    <x-button type="button" variant="secondary" wire:click="cancel">Cancel</x-button>
                    <x-button type="submit">{{ $editingId ? 'Save changes' : 'Add employee' }}</x-button>
                </div>
            </form>
        </x-modal>
    @endcan
</div>
