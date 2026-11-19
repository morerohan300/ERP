<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Support\Policies\ModulePolicy;

/**
 * Authorization for custom field definitions (permissions: core.custom_field.*).
 */
class CustomFieldPolicy extends ModulePolicy
{
    protected string $permission = 'core.custom_field';
}
