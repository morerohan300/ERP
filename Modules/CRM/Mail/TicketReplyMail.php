<?php

declare(strict_types=1);

namespace Modules\CRM\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** A staff reply on a ticket, emailed to the customer. */
class TicketReplyMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly string $ticketNumber,
        public readonly string $ticketSubject,
        public readonly string $body,
        public readonly string $agentName,
        public readonly ?string $portalUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "[{$this->ticketNumber}] {$this->ticketSubject}");
    }

    public function content(): Content
    {
        return new Content(view: 'crm::mail.ticket-reply');
    }
}
