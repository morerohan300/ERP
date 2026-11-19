<?php

declare(strict_types=1);

namespace App\Support\Policies;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Database\Eloquent\Model;

/**
 * Permission-driven policy base. Subclasses set $permission to
 * "{module}.{entity}" and get view/create/edit/delete/export abilities that
 * map to "{module}.{entity}.{action}". Record-level checks additionally
 * verify the record belongs to the active company (defence in depth on
 * top of the global scope).
 */
abstract class ModulePolicy
{
    protected string $permission;

    public function viewAny(User $user): bool
    {
        return $user->can("{$this->permission}.view");
    }

    public function view(User $user, Model $model): bool
    {
        return $this->sameCompany($model) && $user->can("{$this->permission}.view");
    }

    public function create(User $user): bool
    {
        return $user->can("{$this->permission}.create");
    }

    public function update(User $user, Model $model): bool
    {
        return $this->sameCompany($model) && $user->can("{$this->permission}.edit");
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->sameCompany($model) && $user->can("{$this->permission}.delete");
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->delete($user, $model);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }

    public function export(User $user): bool
    {
        return $user->can("{$this->permission}.export");
    }

    public function import(User $user): bool
    {
        return $user->can("{$this->permission}.import");
    }

    public function approve(User $user, Model $model): bool
    {
        return $this->sameCompany($model) && $user->can("{$this->permission}.approve");
    }

    protected function sameCompany(Model $model): bool
    {
        if (! array_key_exists('company_id', $model->getAttributes())) {
            return true;
        }

        return (int) $model->getAttribute('company_id') === app(CurrentCompany::class)->id();
    }
}
