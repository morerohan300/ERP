<?php

declare(strict_types=1);

namespace Modules\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Core\Models\ApprovalRequest;

/**
 * Sent to every approver of the request's current step.
 */
class ApprovalPendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ApprovalRequest $request) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->documentLabel();

        return (new MailMessage)
            ->subject(__('Approval required: :doc', ['doc' => $label]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__(':requester submitted :doc for your approval.', [
                'requester' => $this->request->requester?->name ?? __('Someone'),
                'doc' => $label,
            ]))
            ->action(__('Review request'), route('approvals.show', $this->request))
            ->line(__('Workflow: :name', ['name' => $this->request->workflow->name]));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Approval required'),
            'body' => __(':doc is waiting for your approval.', ['doc' => $this->documentLabel()]),
            'url' => route('approvals.show', $this->request, false),
            'icon' => 'check-badge',
            'company_id' => $this->request->company_id,
        ];
    }

    private function documentLabel(): string
    {
        $doc = $this->request->approvable;

        return $doc !== null && method_exists($doc, 'approvalLabel') ? $doc->approvalLabel() : $this->request->number;
    }
}
