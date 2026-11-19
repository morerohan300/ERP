<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Support\Policies\ModulePolicy;

/** Authorization for email templates (permissions: crm.email_template.*). */
class EmailTemplatePolicy extends ModulePolicy
{
    protected string $permission = 'crm.email_template';
}
