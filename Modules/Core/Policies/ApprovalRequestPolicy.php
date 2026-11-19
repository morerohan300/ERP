<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Modules\Core\Models\ApprovalRequest;
use Modules\Core\Services\ApprovalService;

class ApprovalRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('core.approval.view');
    }

    /** Requesters, approvers and users with approval rights may view a request. */
    public function view(User $user, ApprovalRequest $request): bool
    {
        if ($request->company_id !== app(CurrentCompany::class)->id()) {
            return false;
        }

        return $request->requested_by === $user->getKey()
            || $user->can('core.approval.approve')
            || app(ApprovalService::class)->canAct($request, $user);
    }

    public function act(User $user, ApprovalRequest $request): bool
    {
        return $request->company_id === app(CurrentCompany::class)->id()
            && app(ApprovalService::class)->canAct($request, $user);
    }

    public function cancel(User $user, ApprovalRequest $request): bool
    {
        return $request->isPending()
            && $request->company_id === app(CurrentCompany::class)->id()
            && ($request->requested_by === $user->getKey() || $user->isOwnerOfCurrentCompany());
    }
}
