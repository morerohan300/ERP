<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Models\User;
use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\HasCustomFields;
use Modules\CRM\Database\Factories\CrmAccountFactory;
use Spatie\Activitylog\LogOptions;

/**
 * An organization tracked in the CRM (distinct from a Sales customer).
 *
 * @property int $id
 * @property string $name
 */
#[Fillable([
    'name', 'legal_name', 'email', 'phone', 'website', 'industry', 'employee_count', 'annual_revenue',
    'tax_number', 'billing_address', 'shipping_address', 'owner_id', 'parent_id',
])]
class CrmAccount extends Model
{
    /** @use HasFactory<CrmAccountFactory> */
    use BelongsToCompany, HasAuditColumns, HasCustomFields, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected $table = 'crm_accounts';

    protected function casts(): array
    {
        return [
            'billing_address' => 'array',
            'shipping_address' => 'array',
            'tax_number' => 'encrypted',
            'annual_revenue' => 'decimal:4',
            'employee_count' => 'integer',
        ];
    }

    protected static function newFactory(): CrmAccountFactory
    {
        return CrmAccountFactory::new();
    }

    /** The tax number is encrypted at rest, so it must never reach the audit log in clear text. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept(['tax_number'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName(static::activityLogName());
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'related');
    }
}
