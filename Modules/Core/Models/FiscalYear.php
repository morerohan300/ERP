<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Core\Database\Factories\FiscalYearFactory;

/**
 * @property string $name
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property bool $is_closed
 */
#[Fillable(['name', 'start_date', 'end_date', 'is_closed', 'closed_at'])]
class FiscalYear extends Model
{
    /** @use HasFactory<FiscalYearFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_closed' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): FiscalYearFactory
    {
        return FiscalYearFactory::new();
    }

    /** @param Builder<FiscalYear> $query */
    public function scopeContaining(Builder $query, CarbonInterface $date): void
    {
        $query->whereDate('start_date', '<=', $date)->whereDate('end_date', '>=', $date);
    }

    public static function forDate(CarbonInterface $date): ?self
    {
        return static::query()->containing($date)->first();
    }

    public function isCurrent(): bool
    {
        return now()->betweenIncluded($this->start_date->startOfDay(), $this->end_date->endOfDay());
    }
}
