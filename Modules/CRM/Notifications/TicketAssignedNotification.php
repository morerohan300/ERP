<?php

declare(strict_types=1);

namespace Modules\CRM\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\CRM\Models\Ticket;

class TicketAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $ticketUuid, private readonly string $number, private readonly string $subject) {}

    public static function for(Ticket $ticket): self
    {
        return new self($ticket->uuid, $ticket->number, $ticket->subject);
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Ticket :number assigned to you', ['number' => $this->number]))
            ->line($this->subject)
            ->action(__('Open ticket'), route('crm.tickets.show', $this->ticketUuid));
    }

    /** @return array<string, string> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Ticket :number assigned to you', ['number' => $this->number]),
            'message' => $this->subject,
            'url' => route('crm.tickets.show', $this->ticketUuid),
        ];
    }
}
