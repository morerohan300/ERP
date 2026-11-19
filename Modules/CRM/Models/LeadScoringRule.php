<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * "If <field> <operator> <value> then add <points>" — applied by LeadScoringService.
 *
 * @property string $field
 * @property string $operator
 * @property ?string $value
 * @property int $points
 */
#[Fillable(['field', 'operator', 'value', 'points', 'is_active'])]
class LeadScoringRule extends Model
{
    use BelongsToCompany, HasAuditColumns, HasUuid, LogsModelActivity, SoftDeletes;

    /** Lead attributes a rule may test. */
    public const FIELDS = [
        'email' => 'Email',
        'phone' => 'Phone',
        'company_name' => 'Company name',
        'title' => 'Title',
        'lead_source_id' => 'Lead source',
        'estimated_value' => 'Estimated value',
        'address.country' => 'Country',
    ];

    public const OPERATORS = [
        'equals' => 'equals',
        'not_equals' => 'does not equal',
        'contains' => 'contains',
        'gt' => 'is greater than',
        'lt' => 'is less than',
        'filled' => 'is filled in',
        'empty' => 'is empty',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @param Builder<LeadScoringRule> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
