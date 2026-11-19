<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use Modules\CRM\Models\Contact;
use Modules\CRM\Models\CrmAccount;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Lead;

/** What a lead conversion produced. */
final class ConversionResult
{
    public function __construct(
        public readonly Lead $lead,
        public readonly Contact $contact,
        public readonly ?CrmAccount $account,
        public readonly ?Deal $deal,
    ) {}
}
