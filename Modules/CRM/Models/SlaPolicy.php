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
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\SlaPolicyFactory;

/**
 * Response and resolution targets for a ticket priority.
 *
 * `business_hours` is null for 24/7 support, otherwise
 * {"timezone": "UTC", "days": {"mon": ["09:00", "17:00"], ...}} — days that are missing are closed.
 *
 * @property int $id
 * @property string $name
 * @property string $priority
 * @property int $first_response_minutes
 * @property int $resolution_minutes
 * @property ?array{timezone?: string, days?: array<string, array{0: string, 1: string}>} $business_hours
 */
#[Fillable(['name', 'priority', 'first_response_minutes', 'resolution_minutes', 'business_hours', 'is_active'])]
class SlaPolicy extends Model
{
    /** @use HasFactory<SlaPolicyFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    public const DAYS = ['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'];

    protected function casts(): array
    {
        return [
            'first_response_minutes' => 'integer',
            'resolution_minutes' => 'integer',
            'business_hours' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): SlaPolicyFactory
    {
        return SlaPolicyFactory::new();
    }

    /** @param Builder<SlaPolicy> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
