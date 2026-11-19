<?php

declare(strict_types=1);

namespace Modules\CRM\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** An HTML email from the CRM (one-to-one email or campaign), with an optional open-tracking pixel. */
class CrmMessageMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly string $subjectLine,
        public readonly string $html,
        public readonly ?string $openUrl = null,
        public readonly ?string $ccAddress = null,
    ) {
        if ($ccAddress !== null && $ccAddress !== '') {
            $this->cc($ccAddress);
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'crm::mail.message');
    }
}
