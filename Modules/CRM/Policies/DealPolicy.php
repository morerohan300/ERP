<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Support\Policies\ModulePolicy;

/** Authorization for deals (permissions: crm.deal.*). Moving, winning and losing a deal need crm.deal.edit. */
class DealPolicy extends ModulePolicy
{
    protected string $permission = 'crm.deal';
}
