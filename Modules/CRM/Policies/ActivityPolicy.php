<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Support\Policies\ModulePolicy;

/** Authorization for activities (permissions: crm.activity.*). */
class ActivityPolicy extends ModulePolicy
{
    protected string $permission = 'crm.activity';
}
