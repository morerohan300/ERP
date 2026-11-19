<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Models\User;
use App\Support\Policies\ModulePolicy;
use Illuminate\Database\Eloquent\Model;

/** Authorization for contacts (permissions: crm.contact.*, plus crm.portal.manage and crm.email.send). */
class ContactPolicy extends ModulePolicy
{
    protected string $permission = 'crm.contact';

    public function managePortal(User $user, Model $model): bool
    {
        return $this->sameCompany($model) && $user->can('crm.portal.manage');
    }

    public function email(User $user, Model $model): bool
    {
        return $this->sameCompany($model) && $user->can('crm.email.send');
    }
}
