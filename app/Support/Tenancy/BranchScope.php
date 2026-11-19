<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Limits branch-bound records to the branches the current user may access.
 * Records with a NULL branch_id (company-wide) stay visible.
 */
final class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $allowed = app(CurrentCompany::class)->allowedBranchIds();

        if ($allowed === null) {
            return;
        }

        $column = $model->qualifyColumn('branch_id');

        $builder->where(function (Builder $query) use ($column, $allowed): void {
            $query->whereNull($column)->orWhereIn($column, $allowed === [] ? [0] : $allowed);
        });
    }
}
