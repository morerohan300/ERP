<?php

declare(strict_types=1);

namespace Modules\CRM\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\CRM\Models\Activity;

class ActivityReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $type, private readonly string $subject, private readonly ?string $when) {}

    public static function for(Activity $activity): self
    {
        return new self($activity->type, $activity->subject, $activity->scheduledAt()?->toDayDateTimeString());
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Reminder: :subject', ['subject' => $this->subject]))
            ->line(__(':type reminder', ['type' => ucfirst($this->type)]))
            ->line($this->subject.($this->when ? ' · '.$this->when : ''))
            ->action(__('Open calendar'), route('crm.calendar'));
    }

    /** @return array<string, string|null> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Reminder: :subject', ['subject' => $this->subject]),
            'message' => $this->when,
            'url' => route('crm.calendar'),
        ];
    }
}
