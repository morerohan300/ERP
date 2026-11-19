<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Models\User;
use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Numbering\HasDocumentNumber;
use App\Support\Tenancy\BelongsToBranch;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\HasCustomFields;
use Modules\CRM\Database\Factories\DealFactory;

/**
 * A sales opportunity moving through a pipeline.
 *
 * @property int $id
 * @property string $number
 * @property string $title
 * @property string $value
 * @property int $pipeline_id
 * @property int $pipeline_stage_id
 * @property int $probability
 * @property ?\Illuminate\Support\Carbon $won_at
 * @property ?\Illuminate\Support\Carbon $lost_at
 */
#[Fillable([
    'branch_id', 'pipeline_id', 'pipeline_stage_id', 'title', 'contact_id', 'crm_account_id', 'customer_id',
    'owner_id', 'value', 'currency_code', 'expected_close_date', 'probability', 'description',
])]
class Deal extends Model
{
    /** @use HasFactory<DealFactory> */
    use BelongsToBranch, BelongsToCompany, HasAuditColumns, HasCustomFields, HasDocumentNumber, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected static string $documentType = 'deal';

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'expected_close_date' => 'date',
            'probability' => 'integer',
            'won_at' => 'datetime',
            'lost_at' => 'datetime',
            'kanban_order' => 'integer',
        ];
    }

    protected static function newFactory(): DealFactory
    {
        return DealFactory::new();
    }

    public function isOpen(): bool
    {
        return $this->won_at === null && $this->lost_at === null;
    }

    /** "open", "won" or "lost". */
    public function outcome(): string
    {
        return match (true) {
            $this->won_at !== null => 'won',
            $this->lost_at !== null => 'lost',
            default => 'open',
        };
    }

    /** @param Builder<Deal> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('won_at')->whereNull('lost_at');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CrmAccount::class, 'crm_account_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(DealProduct::class)->orderBy('sort_order');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'related');
    }
}
