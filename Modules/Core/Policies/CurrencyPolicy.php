<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Support\Policies\ModulePolicy;

/**
 * Authorization for company currencies and exchange rates (permissions: core.currency.*).
 */
class CurrencyPolicy extends ModulePolicy
{
    protected string $permission = 'core.currency';
}
