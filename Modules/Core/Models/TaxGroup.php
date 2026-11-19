<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Combination of taxes applied together, e.g. CGST 9% + SGST 9%.
 */
#[Fillable(['name', 'code', 'is_active'])]
class TaxGroup extends Model
{
    use BelongsToCompany, HasAuditColumns, HasUuid, LogsModelActivity, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'tax_group_tax')->withPivot('sequence')->orderByPivot('sequence');
    }

    /** Effective combined percentage of all percent-type taxes in the group. */
    public function combinedRate(): string
    {
        return $this->taxes
            ->where('type', 'percent')
            ->reduce(fn (string $carry, Tax $tax) => bcadd($carry, (string) $tax->rate, 4), '0.0000');
    }
}
