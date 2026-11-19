<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Support\Policies\ModulePolicy;

/** Authorization for segments (permissions: crm.segment.*). */
class SegmentPolicy extends ModulePolicy
{
    protected string $permission = 'crm.segment';
}
