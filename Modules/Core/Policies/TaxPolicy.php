<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Support\Policies\ModulePolicy;

/**
 * Authorization for taxes and tax groups (permissions: core.tax.*).
 */
class TaxPolicy extends ModulePolicy
{
    protected string $permission = 'core.tax';
}
