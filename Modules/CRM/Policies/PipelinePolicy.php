<?php

declare(strict_types=1);

namespace Modules\CRM\Policies;

use App\Models\User;
use App\Support\Policies\ModulePolicy;
use Illuminate\Database\Eloquent\Model;
use Modules\CRM\Models\Pipeline;

/** Authorization for pipelines (permissions: crm.pipeline.*). A pipeline that holds deals cannot be deleted. */
class PipelinePolicy extends ModulePolicy
{
    protected string $permission = 'crm.pipeline';

    public function delete(User $user, Model $model): bool
    {
        /** @var Pipeline $model */
        return ! $model->is_default && ! $model->deals()->exists() && parent::delete($user, $model);
    }
}
