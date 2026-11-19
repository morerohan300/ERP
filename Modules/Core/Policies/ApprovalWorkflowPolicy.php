<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Support\Policies\ModulePolicy;

/**
 * Authorization for approval workflow definitions (permissions: core.approval_workflow.*).
 */
class ApprovalWorkflowPolicy extends ModulePolicy
{
    protected string $permission = 'core.approval_workflow';
}
