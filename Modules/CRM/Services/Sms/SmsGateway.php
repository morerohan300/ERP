<?php

declare(strict_types=1);

namespace Modules\CRM\Services\Sms;

/**
 * Sends a text message. Bind another implementation in the container (and set
 * CRM_SMS_DRIVER) to use a real provider; the default driver only writes to the log.
 */
interface SmsGateway
{
    /** @throws \RuntimeException when the message cannot be sent */
    public function send(string $to, string $message): void;
}
