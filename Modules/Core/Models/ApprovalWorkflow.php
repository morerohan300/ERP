<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $document_type
 * @property array<int, array{field: string, operator: string, value: mixed}>|null $conditions
 */
#[Fillable(['document_type', 'name', 'description', 'conditions', 'priority', 'is_active'])]
class ApprovalWorkflow extends Model
{
    use BelongsToCompany, HasAuditColumns, HasUuid, LogsModelActivity, SoftDeletes;

    public const OPERATORS = ['=', '!=', '>', '>=', '<', '<=', 'in', 'not_in'];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_order');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class);
    }

    /** @param Builder<ApprovalWorkflow> $query */
    public function scopeActiveFor(Builder $query, string $documentType): void
    {
        $query->where('document_type', $documentType)->where('is_active', true)->orderBy('priority');
    }

    /** True when every condition matches the given document. */
    public function matches(Model $document): bool
    {
        foreach ($this->conditions ?? [] as $condition) {
            $actual = data_get($document, $condition['field']);
            $expected = $condition['value'];

            $passes = match ($condition['operator']) {
                '=' => $actual == $expected,
                '!=' => $actual != $expected,
                '>' => is_numeric($actual) && bccomp((string) $actual, (string) $expected, 4) === 1,
                '>=' => is_numeric($actual) && bccomp((string) $actual, (string) $expected, 4) >= 0,
                '<' => is_numeric($actual) && bccomp((string) $actual, (string) $expected, 4) === -1,
                '<=' => is_numeric($actual) && bccomp((string) $actual, (string) $expected, 4) <= 0,
                'in' => in_array($actual, (array) $expected, false),
                'not_in' => ! in_array($actual, (array) $expected, false),
                default => false,
            };

            if (! $passes) {
                return false;
            }
        }

        return true;
    }
}
