<div>
    @if (! $this->employee)
        <p class="text-sm text-slate-400">No employee record is linked to your account yet — contact HR.</p>
    @else
        @php($employee = $this->employee)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <p class="text-xs text-slate-500">Employee number</p>
                <p class="mt-0.5 text-sm font-medium text-slate-900">{{ $employee->employee_number }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Staff category</p>
                <p class="mt-0.5 text-sm font-medium text-slate-900">{{ ucwords(str_replace('_', ' ', $employee->staff_category->value)) }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Hire date</p>
                <p class="mt-0.5 text-sm font-medium text-slate-900">{{ $employee->hire_date->toFormattedDateString() }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Department</p>
                <p class="mt-0.5 text-sm font-medium text-slate-900">{{ $employee->department?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Position</p>
                <p class="mt-0.5 text-sm font-medium text-slate-900">{{ $employee->position?->title ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Duty station</p>
                <p class="mt-0.5 text-sm font-medium text-slate-900">{{ $employee->dutyStation?->name ?? '—' }}</p>
            </div>
        </div>

        <div class="mt-6 border-t border-slate-100 pt-4">
            <h3 class="text-sm font-semibold text-slate-700">Contract history</h3>
            <ul class="mt-2 divide-y divide-slate-100">
                @forelse ($employee->contracts as $contract)
                    <li class="py-2.5 text-sm">
                        <span class="font-medium text-slate-900">{{ ucwords(str_replace('_', ' ', $contract->type->value)) }}</span>
                        <span class="ml-2 text-xs text-slate-500">{{ $contract->salaryGrade->name }} ({{ $contract->salaryGrade->code }})</span>
                        <span class="ml-2 text-xs text-slate-400">
                            from {{ $contract->start_date->toFormattedDateString() }}
                            @if ($contract->end_date) to {{ $contract->end_date->toFormattedDateString() }} @endif
                        </span>
                        <span @class([
                            'ml-2 rounded-full px-2 py-0.5 text-[11px] font-semibold',
                            'bg-emerald-50 text-emerald-700' => $contract->status->value === 'active',
                            'bg-slate-100 text-slate-500' => $contract->status->value !== 'active',
                        ])>
                            {{ ucfirst($contract->status->value) }}
                        </span>
                    </li>
                @empty
                    <li class="py-2.5 text-sm text-slate-400">No contracts on file.</li>
                @endforelse
            </ul>
        </div>
    @endif
</div>
