<?php

namespace App\Notifications\Approvals;

use App\Models\ApprovalInstance;
use App\Models\ApprovalInstanceStep;
use App\Support\Approvals\ApprovalStatus;
use App\Support\Approvals\ApprovalStepStatus;
use App\Support\Notifications\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells the requester what just happened to their request: one step
 * decided, whether that finished the whole thing or moved it on to the
 * next step. Sent to the requester only, on every ApprovalStepActedOn —
 * see docs/build/00-build-plan.md Step 0.7.
 *
 * Sent synchronously — see App\Notifications\Approvals\ApprovalNeeded's
 * docblock for why.
 */
class ApprovalDecisionMade extends Notification
{
    use Queueable;

    public function __construct(
        public readonly ApprovalInstance $instance,
        public readonly ApprovalInstanceStep $step,
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
        return (new MailMessage)
            ->subject('Update on your request: '.$this->subjectLabel())
            ->line($this->message())
            ->action('View status', route('approvals-demo'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'notification_type' => NotificationType::ApprovalDecision->value,
            'approval_instance_id' => $this->instance->id,
            'message' => $this->message(),
        ];
    }

    private function message(): string
    {
        $verb = $this->step->status === ApprovalStepStatus::Approved ? 'approved' : 'rejected';
        $label = $this->subjectLabel();

        if ($this->instance->status === ApprovalStatus::Rejected) {
            return "\"{$label}\" was rejected at {$this->step->label}.";
        }

        if ($this->instance->status === ApprovalStatus::Approved) {
            return "\"{$label}\" is fully approved.";
        }

        return "{$this->step->label} {$verb} \"{$label}\" — now awaiting {$this->instance->currentStep()?->label}.";
    }

    private function subjectLabel(): string
    {
        $subject = $this->instance->subject;

        return $subject?->getAttribute('title')
            ?? $subject?->getAttribute('name')
            ?? class_basename($subject).' #'.$this->instance->subject_id;
    }
}
