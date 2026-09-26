<?php

namespace App\Livewire\Approvals;

use App\Models\ApprovalChain;
use App\Models\ApprovalInstance;
use App\Models\TestRequest;
use App\Support\Approvals\ApprovalWorkflow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use RuntimeException;

/**
 * A working, end-to-end proof of the approval engine (Build Plan Step
 * 0.6's Definition of Done) — not a real module screen. Submit a
 * TestRequest, watch it move through a 2-step chain, approve/reject as
 * whoever the current step needs. Real leave/payroll/procurement
 * approval screens in later phases reuse App\Support\Approvals\
 * ApprovalWorkflow and the <x-approval-status> component this uses, not
 * this page itself.
 */
class Demo extends Component
{
    public string $title = '';

    public ?string $reason = null;

    public array $comments = [];

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'reason' => 'nullable|string|max:1000',
        ];
    }

    #[Computed]
    public function myRequests(): Collection
    {
        return TestRequest::with('approvalInstance.steps.actor')
            ->where('requester_id', Auth::id())
            ->latest()
            ->get();
    }

    #[Computed]
    public function awaitingMyApproval(): Collection
    {
        return app(ApprovalWorkflow::class)->awaitingActionBy(Auth::user());
    }

    public function submit(): void
    {
        $validated = $this->validate();

        $chain = ApprovalChain::where('action_type', 'test_request')->where('is_active', true)->first();

        if (! $chain) {
            $this->addError('title', 'No approval chain is configured for test requests yet.');

            return;
        }

        $request = TestRequest::create([...$validated, 'requester_id' => Auth::id()]);

        app(ApprovalWorkflow::class)->submit($chain, $request, Auth::user());

        unset($this->myRequests);
        $this->reset(['title', 'reason']);
    }

    public function approve(int $instanceId): void
    {
        $instance = ApprovalInstance::findOrFail($instanceId);

        try {
            app(ApprovalWorkflow::class)->approve($instance, Auth::user(), $this->comments[$instanceId] ?? null);
        } catch (RuntimeException $e) {
            // Most likely someone else already acted on this step between
            // page load and this click — the "awaiting my approval" list
            // was correct when rendered, just stale by now.
            $this->addError('approval', $e->getMessage());
        }

        unset($this->awaitingMyApproval, $this->myRequests);
        unset($this->comments[$instanceId]);
    }

    public function reject(int $instanceId): void
    {
        $instance = ApprovalInstance::findOrFail($instanceId);

        try {
            app(ApprovalWorkflow::class)->reject($instance, Auth::user(), $this->comments[$instanceId] ?? null);
        } catch (RuntimeException $e) {
            $this->addError('approval', $e->getMessage());
        }

        unset($this->awaitingMyApproval, $this->myRequests);
        unset($this->comments[$instanceId]);
    }

    public function render()
    {
        return view('livewire.approvals.demo');
    }
}
