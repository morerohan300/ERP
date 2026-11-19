<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Support\Policies\ModulePolicy;

/** Authorization for SLA policies (permissions: crm.sla_policy.*). */
class SlaPolicyPolicy extends ModulePolicy
{
    protected string $permission = 'crm.sla_policy';
}
