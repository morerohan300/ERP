<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Models\User;
use App\Support\Policies\ModulePolicy;
use Illuminate\Database\Eloquent\Model;
use Modules\CRM\Models\Campaign;

/** Authorization for campaigns (permissions: crm.campaign.*). Sent campaigns are read-only. */
class CampaignPolicy extends ModulePolicy
{
    protected string $permission = 'crm.campaign';

    public function update(User $user, Model $model): bool
    {
        /** @var Campaign $model */
        return $model->isEditable() && parent::update($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        /** @var Campaign $model */
        return $model->status !== Campaign::STATUS_SENDING && parent::delete($user, $model);
    }

    public function send(User $user, Model $model): bool
    {
        /** @var Campaign $model */
        return $model->isEditable() && $this->sameCompany($model) && $user->can('crm.campaign.send');
    }
}
