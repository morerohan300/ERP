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
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\HasCustomFields;
use Modules\CRM\Database\Factories\LeadFactory;
use Modules\CRM\Services\LeadScoringService;

/**
 * A potential customer that has not been qualified yet. Converting a lead
 * creates a contact (and optionally an account and a deal).
 *
 * @property int $id
 * @property string $number
 * @property string $first_name
 * @property ?string $last_name
 * @property ?string $company_name
 * @property ?string $email
 * @property int $score
 * @property ?\Illuminate\Support\Carbon $converted_at
 */
#[Fillable([
    'branch_id', 'title', 'first_name', 'last_name', 'company_name', 'email', 'phone', 'lead_source_id',
    'lead_status_id', 'owner_id', 'estimated_value', 'currency_code', 'address', 'description', 'lost_reason',
])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use BelongsToBranch, BelongsToCompany, HasAuditColumns, HasCustomFields, HasDocumentNumber, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected static string $documentType = 'lead';

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'estimated_value' => 'decimal:4',
            'score' => 'integer',
            'converted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $lead): void {
            $lead->score = app(LeadScoringService::class)->score($lead);
        });
    }

    protected static function newFactory(): LeadFactory
    {
        return LeadFactory::new();
    }

    /** @return Attribute<string, never> */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim($this->first_name.' '.($this->last_name ?? '')));
    }

    public function isConverted(): bool
    {
        return $this->converted_at !== null;
    }

    /** @param Builder<Lead> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('converted_at');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function convertedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'converted_contact_id');
    }

    public function convertedAccount(): BelongsTo
    {
        return $this->belongsTo(CrmAccount::class, 'converted_account_id');
    }

    public function convertedDeal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'converted_deal_id');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'related');
    }

    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'emailable');
    }
}
