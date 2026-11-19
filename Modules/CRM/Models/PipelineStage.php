<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\PipelineStageFactory;

/**
 * @property int $id
 * @property int $pipeline_id
 * @property string $name
 * @property int $probability
 * @property bool $is_won
 * @property bool $is_lost
 */
#[Fillable(['pipeline_id', 'name', 'probability', 'sort_order', 'is_won', 'is_lost', 'color'])]
class PipelineStage extends Model
{
    /** @use HasFactory<PipelineStageFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'probability' => 'integer',
            'sort_order' => 'integer',
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
        ];
    }

    protected static function newFactory(): PipelineStageFactory
    {
        return PipelineStageFactory::new();
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function isClosed(): bool
    {
        return $this->is_won || $this->is_lost;
    }

    /** @param Builder<PipelineStage> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->where('is_won', false)->where('is_lost', false);
    }
}
