<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Support\Policies\ModulePolicy;

/**
 * Authorization for departments (permissions: core.department.*).
 */
class DepartmentPolicy extends ModulePolicy
{
    protected string $permission = 'core.department';
}
