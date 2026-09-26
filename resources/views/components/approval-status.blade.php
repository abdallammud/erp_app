@props(['instance'])

{{--
    Reusable across every future approval UI (leave, payroll, requisition,
    etc.) — see docs/build/00-build-plan.md Step 0.6. Don't rebuild this
    per module; pass any ApprovalInstance in.
--}}
<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @foreach ($instance->steps as $step)
        @php
            $badge = match ($step->status) {
                \App\Support\Approvals\ApprovalStepStatus::Approved => ['bg-emerald-50 text-emerald-700', 'Approved'],
                \App\Support\Approvals\ApprovalStepStatus::Rejected => ['bg-red-50 text-red-700', 'Rejected'],
                \App\Support\Approvals\ApprovalStepStatus::Skipped => ['bg-slate-100 text-slate-400', 'Skipped'],
                default => $instance->current_sequence === $step->sequence
                    ? ['bg-amber-50 text-amber-700', 'Pending']
                    : ['bg-slate-100 text-slate-400', 'Waiting'],
            };
        @endphp
        <div class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2 text-sm">
            <div>
                <span class="font-medium text-slate-900">{{ $step->sequence }}. {{ $step->label ?? $step->approver_role }}</span>
                @if ($step->actor)
                    <span class="ml-2 text-xs text-slate-500">by {{ $step->actor->name }}</span>
                @endif
                @if ($step->comment)
                    <p class="mt-0.5 text-xs italic text-slate-500">"{{ $step->comment }}"</p>
                @endif
            </div>
            <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $badge[0] }}">
                {{ $badge[1] }}
            </span>
        </div>
    @endforeach
</div>
