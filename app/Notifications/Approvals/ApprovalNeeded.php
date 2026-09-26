<?php

namespace App\Notifications\Approvals;

use App\Models\ApprovalInstance;
use App\Support\Notifications\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Something is waiting on you to approve." Sent to every eligible
 * approver of $instance's current step — see
 * App\Support\Approvals\ApprovalWorkflow::eligibleApprovers() and
 * docs/build/00-build-plan.md Step 0.7.
 *
 * Sent synchronously (not ShouldQueue): QUEUE_CONNECTION=database has no
 * worker running in this environment, so a queued notification would
 * silently never appear. Revisit once a real queue worker is part of
 * the deploy story.
 */
class ApprovalNeeded extends Notification
{
    use Queueable;

    public function __construct(
        public readonly ApprovalInstance $instance,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $step = $this->instance->currentStep();

        return (new MailMessage)
            ->subject('Approval needed: '.$this->subjectLabel())
            ->line("A {$step?->label} is waiting on your decision.")
            ->line('Requested by: '.$this->instance->requester?->name)
            ->action('Review request', route('approvals-demo'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $step = $this->instance->currentStep();

        return [
            'notification_type' => NotificationType::ApprovalNeeded->value,
            'approval_instance_id' => $this->instance->id,
            'message' => "{$step?->label} needed on \"{$this->subjectLabel()}\"",
        ];
    }

    private function subjectLabel(): string
    {
        $subject = $this->instance->subject;

        return $subject?->getAttribute('title')
            ?? $subject?->getAttribute('name')
            ?? class_basename($subject).' #'.$this->instance->subject_id;
    }
}
