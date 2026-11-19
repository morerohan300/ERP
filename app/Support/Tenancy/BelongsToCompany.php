<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Company;

/**
 * Makes a model tenant-aware: adds the CompanyScope global scope and
 * fills company_id from the active company on create.
 *
 * @mixin Model
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model): void {
            if (empty($model->company_id)) {
                $model->company_id = app(CurrentCompany::class)->getOrFail()->getKey();
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Query across all companies — only for system-level code (e.g. scheduler). */
    public static function withoutCompanyScope(): Builder
    {
        return static::withoutGlobalScope(CompanyScope::class);
    }
}
