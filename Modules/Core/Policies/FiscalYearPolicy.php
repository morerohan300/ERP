<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Models\User;
use App\Support\Policies\ModulePolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Authorization for fiscal years (permissions: core.fiscal_year.*).
 * Closed years are read-only.
 */
class FiscalYearPolicy extends ModulePolicy
{
    protected string $permission = 'core.fiscal_year';

    public function update(User $user, Model $model): bool
    {
        return ! $model->getAttribute('is_closed') && parent::update($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return ! $model->getAttribute('is_closed') && parent::delete($user, $model);
    }
}
