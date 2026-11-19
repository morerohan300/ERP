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
use Modules\CRM\Database\Factories\PipelineFactory;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_default
 */
#[Fillable(['name', 'is_default'])]
class Pipeline extends Model
{
    /** @use HasFactory<PipelineFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    protected static function newFactory(): PipelineFactory
    {
        return PipelineFactory::new();
    }

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('sort_order');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /** The company's default pipeline, falling back to the first one. */
    public static function default(): ?self
    {
        return static::query()->orderByDesc('is_default')->orderBy('id')->first();
    }
}
