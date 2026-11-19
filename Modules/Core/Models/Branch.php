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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Factories\BranchFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property bool $is_head_office
 */
#[Fillable(['name', 'code', 'email', 'phone', 'address', 'is_head_office', 'is_active'])]
class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'is_head_office' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): BranchFactory
    {
        return BranchFactory::new();
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /** @param Builder<Branch> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
