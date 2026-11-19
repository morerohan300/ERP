<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Support\Policies\ModulePolicy;

/**
 * Authorization for branches (permissions: core.branch.*).
 */
class BranchPolicy extends ModulePolicy
{
    protected string $permission = 'core.branch';
}
