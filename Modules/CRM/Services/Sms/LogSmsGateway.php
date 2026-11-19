<?php

declare(strict_types=1);

namespace Modules\CRM\Services\Sms;

use Illuminate\Support\Facades\Log;

/** Default SMS driver: records the message in the application log instead of sending it. */
final class LogSmsGateway implements SmsGateway
{
    public function send(string $to, string $message): void
    {
        Log::info('CRM SMS (log driver)', ['to' => $to, 'message' => $message]);
    }
}
