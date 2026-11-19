<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Factories\TaxFactory;

/**
 * A single tax rate (GST, VAT, sales tax ...).
 *
 * @property string $name
 * @property string $code
 * @property string $rate
 * @property string $type percent|fixed
 * @property bool $is_inclusive
 * @property bool $is_compound
 */
#[Fillable([
    'name', 'code', 'rate', 'type', 'scope', 'is_compound', 'is_inclusive',
    'sales_account_id', 'purchase_account_id', 'description', 'is_active',
])]
class Tax extends Model
{
    /** @use HasFactory<TaxFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'is_compound' => 'boolean',
            'is_inclusive' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): TaxFactory
    {
        return TaxFactory::new();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(TaxGroup::class, 'tax_group_tax')->withPivot('sequence');
    }

    /** @param Builder<Tax> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param Builder<Tax> $query */
    public function scopeForSales(Builder $query): void
    {
        $query->whereIn('scope', ['sales', 'both']);
    }

    /** @param Builder<Tax> $query */
    public function scopeForPurchases(Builder $query): void
    {
        $query->whereIn('scope', ['purchase', 'both']);
    }

    public function label(): string
    {
        $rate = rtrim(rtrim((string) $this->rate, '0'), '.');

        return $this->type === 'percent' ? "{$this->name} ({$rate}%)" : "{$this->name} ({$rate})";
    }
}
