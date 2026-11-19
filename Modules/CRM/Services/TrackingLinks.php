<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use Illuminate\Support\Facades\URL;
use Modules\CRM\Models\CampaignRecipient;

/**
 * Builds the signed open/click tracking URLs for campaign emails. The signature
 * covers the destination URL, so a tracking link cannot be turned into an open redirect.
 */
class TrackingLinks
{
    public function openUrl(CampaignRecipient $recipient): string
    {
        return URL::signedRoute('crm.track.open', ['recipient' => $recipient->getKey()]);
    }

    public function clickUrl(CampaignRecipient $recipient, string $destination): string
    {
        return URL::signedRoute('crm.track.click', ['recipient' => $recipient->getKey(), 'url' => $destination]);
    }

    /** Point every http(s) link in an HTML body at a tracked redirect. */
    public function rewriteLinks(string $html, CampaignRecipient $recipient): string
    {
        return (string) preg_replace_callback('/href="(https?:\/\/[^"]+)"/i', function (array $match) use ($recipient): string {
            $destination = html_entity_decode($match[1], ENT_QUOTES);

            return 'href="'.e($this->clickUrl($recipient, $destination)).'"';
        }, $html);
    }

    /** Only plain web destinations may be redirected to. */
    public function isSafeDestination(string $url): bool
    {
        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true)
            && parse_url($url, PHP_URL_HOST) !== null;
    }
}
