<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Modules\CRM\Jobs\SendCampaign;
use Modules\CRM\Mail\CrmMessageMail;
use Modules\CRM\Models\Campaign;
use Modules\CRM\Models\CampaignRecipient;
use Modules\CRM\Services\Sms\SmsGateway;
use Throwable;

/**
 * Campaign sending: builds the recipient list from the segment, delivers each
 * message and keeps the delivery statistics.
 */
class CampaignService
{
    public function __construct(
        private readonly SegmentService $segments,
        private readonly EmailTemplateService $templates,
        private readonly TrackingLinks $tracking,
        private readonly SmsGateway $sms,
    ) {}

    /** Send now: lock in the recipients and hand delivery to the queue. */
    public function send(Campaign $campaign): Campaign
    {
        $this->assertReady($campaign);

        $campaign->forceFill(['status' => Campaign::STATUS_SENDING, 'scheduled_at' => null])->save();
        $this->prepareRecipients($campaign);

        SendCampaign::dispatch($campaign->company_id, $campaign->getKey());

        return $campaign;
    }

    /** Schedule for later; the scheduler dispatches it when due. */
    public function schedule(Campaign $campaign, Carbon $at): Campaign
    {
        $this->assertReady($campaign);

        if ($at->isPast()) {
            throw ValidationException::withMessages(['scheduled_at' => __('Choose a time in the future.')]);
        }

        $campaign->forceFill(['status' => Campaign::STATUS_SCHEDULED, 'scheduled_at' => $at])->save();

        return $campaign;
    }

    public function cancel(Campaign $campaign): Campaign
    {
        if (! $campaign->isEditable()) {
            throw ValidationException::withMessages(['campaign' => __('Only a draft or scheduled campaign can be cancelled.')]);
        }

        $campaign->forceFill(['status' => Campaign::STATUS_CANCELLED, 'scheduled_at' => null])->save();

        return $campaign;
    }

    /**
     * Resolve the segment into recipient rows: contacts with an address who have not opted out.
     * Safe to call again after a partial failure; existing rows are kept.
     */
    public function prepareRecipients(Campaign $campaign): int
    {
        $query = $this->segments->contacts($campaign->segment)->where('do_not_email', false);

        $campaign->type === 'sms'
            ? $query->where(fn ($q) => $q->whereNotNull('mobile')->orWhereNotNull('phone'))
            : $query->whereNotNull('email')->where('email', '!=', '');

        $query->select('contacts.id')->chunkById(500, function ($contacts) use ($campaign): void {
            CampaignRecipient::query()->insertOrIgnore($contacts->map(fn ($contact): array => [
                'company_id' => $campaign->company_id,
                'campaign_id' => $campaign->getKey(),
                'contact_id' => $contact->getKey(),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ])->all());
        }, 'contacts.id', 'id');

        return $campaign->recipients()->count();
    }

    /** Deliver every pending recipient. Called by the SendCampaign job. */
    public function deliver(Campaign $campaign): Campaign
    {
        $template = $campaign->type === 'email' ? $campaign->template : null;

        if ($campaign->type === 'email' && $template === null) {
            $campaign->forceFill(['status' => Campaign::STATUS_FAILED])->save();

            return $campaign;
        }

        $campaign->recipients()->where('status', 'pending')->with('contact.account')->chunkById(100, function ($recipients) use ($campaign, $template): void {
            foreach ($recipients as $recipient) {
                $this->deliverTo($campaign, $recipient, $template);
            }
        });

        $campaign->forceFill(['status' => Campaign::STATUS_SENT, 'sent_at' => now()])->save();

        return $this->refreshStats($campaign);
    }

    /** @return array<string, int> */
    public function stats(Campaign $campaign): array
    {
        $recipients = $campaign->recipients();

        return [
            'total' => (clone $recipients)->count(),
            'sent' => (clone $recipients)->where('status', 'sent')->count(),
            'failed' => (clone $recipients)->where('status', 'failed')->count(),
            'opened' => (clone $recipients)->whereNotNull('opened_at')->count(),
            'clicked' => (clone $recipients)->whereNotNull('clicked_at')->count(),
            'bounced' => (clone $recipients)->whereNotNull('bounced_at')->count(),
        ];
    }

    public function refreshStats(Campaign $campaign): Campaign
    {
        $campaign->forceFill(['stats' => $this->stats($campaign)])->save();

        return $campaign;
    }

    private function deliverTo(Campaign $campaign, CampaignRecipient $recipient, $template): void
    {
        $contact = $recipient->contact;

        try {
            if ($campaign->type === 'sms') {
                $rendered = $this->templates->render((string) $campaign->name, (string) $campaign->sms_body, $this->templates->dataFor($contact));
                $this->sms->send((string) ($contact->mobile ?: $contact->phone), html_entity_decode(strip_tags($rendered['body'])));
            } else {
                $rendered = $this->templates->render($template, null, $this->templates->dataFor($contact));

                Mail::to($contact->email)->send(new CrmMessageMail(
                    $rendered['subject'],
                    $this->tracking->rewriteLinks($rendered['body'], $recipient),
                    $this->tracking->openUrl($recipient),
                ));
            }

            $recipient->forceFill(['status' => 'sent', 'sent_at' => now(), 'error' => null])->save();
        } catch (Throwable $e) {
            $recipient->forceFill(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 500)])->save();
        }
    }

    private function assertReady(Campaign $campaign): void
    {
        if (! $campaign->isEditable()) {
            throw ValidationException::withMessages(['campaign' => __('This campaign has already been sent or cancelled.')]);
        }

        if ($campaign->segment_id === null) {
            throw ValidationException::withMessages(['segment_id' => __('Choose a segment to send to.')]);
        }

        if ($campaign->type === 'email' && $campaign->email_template_id === null) {
            throw ValidationException::withMessages(['email_template_id' => __('Choose an email template.')]);
        }

        if ($campaign->type === 'sms' && blank($campaign->sms_body)) {
            throw ValidationException::withMessages(['sms_body' => __('Write the text message.')]);
        }
    }
}
