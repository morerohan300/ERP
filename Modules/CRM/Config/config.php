<?php

declare(strict_types=1);

use Modules\CRM\Services\Sms\LogSmsGateway;

return [
    /*
    | Class used to send SMS campaigns. It must implement
    | Modules\CRM\Services\Sms\SmsGateway. The default writes to the log.
    */
    'sms_gateway' => env('CRM_SMS_GATEWAY', LogSmsGateway::class),

    /*
    | Activities whose reminder time has passed are notified by `crm:send-reminders`.
    */
    'reminder_lookback_minutes' => 120,
];
