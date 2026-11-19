<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\LeadStatusFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $color
 * @property bool $is_converted
 * @property bool $is_lost
 */
#[Fillable(['name', 'color', 'sort_order', 'is_converted', 'is_lost'])]
class LeadStatus extends Model
{
    /** @use HasFactory<LeadStatusFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_converted' => 'boolean',
            'is_lost' => 'boolean',
        ];
    }

    protected static function newFactory(): LeadStatusFactory
    {
        return LeadStatusFactory::new();
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** The status a lead takes once it has been converted (null when the company has none). */
    public static function forConversion(): ?self
    {
        return static::query()->where('is_converted', true)->orderBy('sort_order')->first();
    }

    /** The status a new lead starts in: the first open status by sort order. */
    public static function initial(): ?self
    {
        return static::query()->where('is_converted', false)->where('is_lost', false)->orderBy('sort_order')->first();
    }
}
