<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Models\User;
use App\Support\Policies\ModulePolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Lead sources, lead statuses, scoring rules and ticket categories share the
 * two settings permissions: crm.settings.view and crm.settings.edit.
 */
class CrmSettingsPolicy extends ModulePolicy
{
    protected string $permission = 'crm.settings';

    public function create(User $user): bool
    {
        return $user->can('crm.settings.edit');
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->update($user, $model);
    }
}
