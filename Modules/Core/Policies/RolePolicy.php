<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('core.role.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->inCompany($role) && $user->can('core.role.view');
    }

    public function create(User $user): bool
    {
        return $user->can('core.role.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $this->inCompany($role) && $user->can('core.role.edit');
    }

    /** The built-in Administrator role cannot be deleted. */
    public function delete(User $user, Role $role): bool
    {
        return $this->inCompany($role) && $role->name !== 'Administrator' && $user->can('core.role.delete');
    }

    private function inCompany(Role $role): bool
    {
        return (int) $role->getAttribute('company_id') === app(CurrentCompany::class)->id();
    }
}
