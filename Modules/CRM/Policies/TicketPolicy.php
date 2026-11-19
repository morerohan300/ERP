<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Support\Policies\ModulePolicy;

/** Authorization for support tickets (permissions: crm.ticket.*). */
class TicketPolicy extends ModulePolicy
{
    protected string $permission = 'crm.ticket';
}
