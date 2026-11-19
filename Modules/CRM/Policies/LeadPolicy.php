<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Models\User;
use App\Support\Policies\ModulePolicy;
use Illuminate\Database\Eloquent\Model;

/** Authorization for leads (permissions: crm.lead.*). */
class LeadPolicy extends ModulePolicy
{
    protected string $permission = 'crm.lead';

    public function update(User $user, Model $model): bool
    {
        return ! $model->getAttribute('converted_at') && parent::update($user, $model);
    }

    public function convert(User $user, Model $model): bool
    {
        return ! $model->getAttribute('converted_at') && $this->sameCompany($model) && $user->can('crm.lead.convert');
    }
}
