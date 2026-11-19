<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Support\Policies\ModulePolicy;

/** Authorization for CRM accounts (permissions: crm.account.*). */
class AccountPolicy extends ModulePolicy
{
    protected string $permission = 'crm.account';
}
