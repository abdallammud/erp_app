<div class="space-y-6">

    @error('approval')
        <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
    @enderror

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Submit a test request</h2>
        <p class="mt-1 text-sm text-slate-500">
            Goes through the 2-step demo chain: Supervisor review, then HR Admin approval.
        </p>
        <form wire:submit="submit" class="mt-4 space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-600">Title</label>
                <input type="text" wire:model="title" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600">Reason (optional)</label>
                <textarea wire:model="reason" rows="2" class="mt-1 block w-full rounded-md border-slate-300 text-sm"></textarea>
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                Submit for approval
            </button>
        </form>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Awaiting my approval</h2>
        <p class="mt-1 text-sm text-slate-500">Requests where you hold the current step's role — and didn't submit it yourself.</p>

        <div class="mt-4 space-y-4">
            @forelse ($this->awaitingMyApproval as $instance)
                <div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">{{ $instance->subject->title }}</p>
                        <span class="text-xs text-slate-500">from {{ $instance->requester->name }}</span>
                    </div>
                    @if ($instance->subject->reason)
                        <p class="mt-1 text-xs text-slate-500">{{ $instance->subject->reason }}</p>
                    @endif

                    <div class="mt-3">
                        <x-approval-status :instance="$instance" />
                    </div>

                    <textarea wire:model="comments.{{ $instance->id }}" rows="1" placeholder="Optional comment"
                              class="mt-3 block w-full rounded-md border-slate-300 text-xs"></textarea>

                    <div class="mt-2 flex gap-2">
                        <button wire:click="approve({{ $instance->id }})" wire:confirm="Approve this request?"
                                class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500">
                            Approve
                        </button>
                        <button wire:click="reject({{ $instance->id }})" wire:confirm="Reject this request?"
                                class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-500">
                            Reject
                        </button>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400">Nothing waiting on you right now.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">My submitted requests</h2>

        <div class="mt-4 space-y-4">
            @forelse ($this->myRequests as $request)
                <div class="rounded-lg border border-slate-100 p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">{{ $request->title }}</p>
                        <span class="text-xs font-medium uppercase tracking-wide text-slate-400">
                            {{ $request->approvalInstance?->status->value }}
                        </span>
                    </div>
                    @if ($request->approvalInstance)
                        <div class="mt-3">
                            <x-approval-status :instance="$request->approvalInstance" />
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-400">You haven't submitted anything yet.</p>
            @endforelse
        </div>
    </div>

</div>
