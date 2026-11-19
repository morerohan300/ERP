<?php

declare(strict_types=1);

namespace Modules\CRM\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CRM\Models\Contact;
use Modules\CRM\Models\CrmAccount;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Lead;

/** Fired after a lead has been turned into a contact (and optionally an account and a deal). */
final class LeadConverted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Lead $lead,
        public readonly Contact $contact,
        public readonly ?CrmAccount $account,
        public readonly ?Deal $deal,
    ) {}
}
