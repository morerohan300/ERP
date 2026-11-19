<?php

declare(strict_types=1);

namespace Modules\Core\Policies;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Modules\Core\Models\Company;

class CompanyPolicy
{
    public function view(User $user, Company $company): bool
    {
        return $user->belongsToCompany($company) && $user->can('core.company.view');
    }

    public function update(User $user, Company $company): bool
    {
        return $company->getKey() === app(CurrentCompany::class)->id() && $user->can('core.company.edit');
    }

    /** Any verified user may create an additional company they will own. */
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    public function switchTo(User $user, Company $company): bool
    {
        return $company->is_active && $user->belongsToCompany($company);
    }
}
