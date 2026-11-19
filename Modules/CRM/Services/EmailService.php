<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Modules\CRM\Jobs\SendCrmEmail;
use Modules\CRM\Mail\CrmMessageMail;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\EmailLog;
use Modules\CRM\Models\EmailTemplate;
use Modules\CRM\Models\Lead;
use Throwable;

/**
 * Sends one-to-one emails to leads and contacts and keeps the email log.
 */
class EmailService
{
    public function __construct(private readonly EmailTemplateService $templates) {}

    /**
     * Queue an email to a lead or contact. The subject and the HTML body may contain merge tags;
     * plain text must be converted with plainToHtml() first.
     *
     * @throws ValidationException when the recipient has no address or opted out
     */
    public function send(Model $recipient, string $subject, string $body, User $by, ?EmailTemplate $template = null, ?string $cc = null): EmailLog
    {
        $address = $recipient->getAttribute('email');

        if (! ($recipient instanceof Lead || $recipient instanceof Contact) || blank($address)) {
            throw ValidationException::withMessages(['email' => __('This record has no email address.')]);
        }

        if ($recipient instanceof Contact && $recipient->do_not_email) {
            throw ValidationException::withMessages(['email' => __('This contact has opted out of email.')]);
        }

        $rendered = $this->templates->render($subject, $body, $this->templates->dataFor($recipient, $by));

        $log = EmailLog::query()->create([
            'emailable_type' => $recipient->getMorphClass(),
            'emailable_id' => $recipient->getKey(),
            'email_template_id' => $template?->getKey(),
            'from_address' => config('mail.from.address'),
            'to_address' => $address,
            'cc_address' => $cc,
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'status' => 'queued',
        ]);

        SendCrmEmail::dispatch($log->company_id, $log->getKey());

        return $log;
    }

    /** Escape typed text and keep its line breaks, so it can be sent as an HTML body. */
    public function plainToHtml(string $text): string
    {
        return nl2br(e($text), false);
    }

    /** Deliver a queued log entry. Called by the SendCrmEmail job inside the owning company. */
    public function deliver(EmailLog $log): void
    {
        try {
            Mail::to($log->to_address)->send(new CrmMessageMail(
                $log->subject,
                $log->body,
                URL::signedRoute('crm.track.email', ['log' => $log->getKey()]),
                $log->cc_address,
            ));

            $log->forceFill(['status' => 'sent', 'sent_at' => now(), 'error' => null])->save();
        } catch (Throwable $e) {
            $log->forceFill(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 500)])->save();
        }
    }
}
