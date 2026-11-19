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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\LeadSourceFactory;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_active
 */
#[Fillable(['name', 'is_active'])]
class LeadSource extends Model
{
    /** @use HasFactory<LeadSourceFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function newFactory(): LeadSourceFactory
    {
        return LeadSourceFactory::new();
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** @param Builder<LeadSource> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
