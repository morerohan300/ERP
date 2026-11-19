<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts every query on a tenant model to the active company.
 *
 * When no company is active (console commands, tests that opt out) the
 * scope is a no-op only if tenancy enforcement is disabled; otherwise it
 * matches nothing so data can never leak across companies.
 */
final class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $current = app(CurrentCompany::class);
        $column = $model->qualifyColumn('company_id');

        if ($current->check()) {
            $builder->where($column, $current->id());

            return;
        }

        if (config('tenancy.enforce', true)) {
            // Fail closed: no company → no rows.
            $builder->whereRaw('1 = 0');
        }
    }
}
