<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Models\User;
use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\HasCustomFields;
use Modules\CRM\Database\Factories\ContactFactory;

/**
 * A person: a customer's employee, a converted lead, a campaign recipient.
 *
 * @property int $id
 * @property string $first_name
 * @property ?string $last_name
 * @property ?string $email
 * @property bool $do_not_email
 */
#[Fillable([
    'crm_account_id', 'first_name', 'last_name', 'job_title', 'email', 'phone', 'mobile', 'address',
    'owner_id', 'date_of_birth', 'do_not_email',
])]
class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use BelongsToCompany, HasAuditColumns, HasCustomFields, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'date_of_birth' => 'date',
            'do_not_email' => 'boolean',
        ];
    }

    protected static function newFactory(): ContactFactory
    {
        return ContactFactory::new();
    }

    /** @return Attribute<string, never> */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim($this->first_name.' '.($this->last_name ?? '')));
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CrmAccount::class, 'crm_account_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(Segment::class, 'segment_contact');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'related');
    }

    public function emailLogs(): MorphMany
    {
        return $this->morphMany(EmailLog::class, 'emailable');
    }

    public function portalUser(): HasMany
    {
        return $this->hasMany(PortalUser::class);
    }
}
