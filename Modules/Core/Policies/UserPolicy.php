<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;

/**
 * Users are global records; access is granted when the target user is a
 * member of the active company and the actor holds core.user.* permissions.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('core.user.view');
    }

    public function view(User $user, User $target): bool
    {
        return $this->inCompany($target) && $user->can('core.user.view');
    }

    public function create(User $user): bool
    {
        return $user->can('core.user.create');
    }

    public function update(User $user, User $target): bool
    {
        return $this->inCompany($target) && $user->can('core.user.edit');
    }

    public function delete(User $user, User $target): bool
    {
        return $this->inCompany($target) && ! $user->is($target) && $user->can('core.user.delete');
    }

    public function export(User $user): bool
    {
        return $user->can('core.user.export');
    }

    public function import(User $user): bool
    {
        return $user->can('core.user.import');
    }

    private function inCompany(User $target): bool
    {
        $companyId = app(CurrentCompany::class)->id();

        return $companyId !== null && $target->belongsToCompany($companyId);
    }
}
