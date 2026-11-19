<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Core\Database\Factories\ExchangeRateFactory;

/**
 * @property string $from_currency
 * @property string $to_currency
 * @property string $rate
 * @property Carbon $effective_date
 */
#[Fillable(['from_currency', 'to_currency', 'rate', 'effective_date', 'source'])]
class ExchangeRate extends Model
{
    /** @use HasFactory<ExchangeRateFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, LogsModelActivity;

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
            'effective_date' => 'date',
        ];
    }

    protected static function newFactory(): ExchangeRateFactory
    {
        return ExchangeRateFactory::new();
    }

    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency', 'code');
    }

    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency', 'code');
    }
}
