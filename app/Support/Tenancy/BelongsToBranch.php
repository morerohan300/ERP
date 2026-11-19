<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Branch;

/**
 * For records that carry a branch_id: applies branch restrictions and
 * defaults branch_id to the user's active branch.
 *
 * @mixin Model
 */
trait BelongsToBranch
{
    public static function bootBelongsToBranch(): void
    {
        static::addGlobalScope(new BranchScope);

        static::creating(function ($model): void {
            if (empty($model->branch_id)) {
                $model->branch_id = app(CurrentCompany::class)->branchId();
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
