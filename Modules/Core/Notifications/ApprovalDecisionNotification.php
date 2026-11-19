<?php

declare(strict_types=1);

namespace Modules\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Core\Models\ApprovalRequest;

/**
 * Sent to the requester when a workflow is approved or rejected.
 */
class ApprovalDecisionNotification extends Notification implements ShouldQueue
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
        $approved = $this->request->status === 'approved';
        $doc = $this->documentLabel();

        $mail = (new MailMessage)
            ->subject($approved ? __(':doc was approved', ['doc' => $doc]) : __(':doc was rejected', ['doc' => $doc]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line($approved
                ? __('Your request :number for :doc has been fully approved.', ['number' => $this->request->number, 'doc' => $doc])
                : __('Your request :number for :doc has been rejected.', ['number' => $this->request->number, 'doc' => $doc]));

        $comment = $this->request->actions()->latest('acted_at')->value('comment');
        if ($comment) {
            $mail->line(__('Comment: :comment', ['comment' => $comment]));
        }

        return $mail->action(__('View request'), route('approvals.show', $this->request));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $approved = $this->request->status === 'approved';

        return [
            'title' => $approved ? __('Request approved') : __('Request rejected'),
            'body' => __(':doc — :status', ['doc' => $this->documentLabel(), 'status' => __($this->request->status)]),
            'url' => route('approvals.show', $this->request, false),
            'icon' => $approved ? 'check-circle' : 'x-circle',
            'company_id' => $this->request->company_id,
        ];
    }

    private function documentLabel(): string
    {
        $doc = $this->request->approvable;

        return $doc !== null && method_exists($doc, 'approvalLabel') ? $doc->approvalLabel() : $this->request->number;
    }
}
