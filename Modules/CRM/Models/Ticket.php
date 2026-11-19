<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Models\User;
use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Numbering\HasDocumentNumber;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\TicketFactory;

/**
 * A customer support request tracked against an SLA policy.
 *
 * @property int $id
 * @property string $number
 * @property string $subject
 * @property string $priority
 * @property string $status
 * @property ?\Illuminate\Support\Carbon $first_response_due_at
 * @property ?\Illuminate\Support\Carbon $resolution_due_at
 * @property ?\Illuminate\Support\Carbon $first_responded_at
 * @property ?\Illuminate\Support\Carbon $resolved_at
 */
#[Fillable([
    'customer_id', 'contact_id', 'subject', 'description', 'ticket_category_id', 'priority', 'status',
    'assigned_to', 'sla_policy_id', 'channel',
])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use BelongsToCompany, HasAuditColumns, HasDocumentNumber, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    protected static string $documentType = 'ticket';

    public const STATUSES = ['open' => 'Open', 'pending' => 'Pending', 'on_hold' => 'On hold', 'resolved' => 'Resolved', 'closed' => 'Closed'];

    public const PRIORITIES = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];

    public const CHANNELS = ['email' => 'Email', 'portal' => 'Portal', 'phone' => 'Phone', 'web' => 'Web'];

    protected function casts(): array
    {
        return [
            'first_response_due_at' => 'datetime',
            'resolution_due_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'sla_breach_notified_at' => 'datetime',
        ];
    }

    protected static function newFactory(): TicketFactory
    {
        return TicketFactory::new();
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['resolved', 'closed'], true);
    }

    /**
     * SLA position of the ticket: "met" once resolved on time, "breached" when a target was missed,
     * "due_soon" when a target is less than an hour away, otherwise "on_track". Null without an SLA.
     */
    public function slaState(): ?string
    {
        if ($this->sla_policy_id === null || ($this->first_response_due_at === null && $this->resolution_due_at === null)) {
            return null;
        }

        $firstResponseMissed = $this->first_response_due_at !== null && match (true) {
            $this->first_responded_at !== null => $this->first_responded_at->greaterThan($this->first_response_due_at),
            default => $this->first_response_due_at->isPast(),
        };

        $resolutionMissed = $this->resolution_due_at !== null && match (true) {
            $this->resolved_at !== null => $this->resolved_at->greaterThan($this->resolution_due_at),
            default => $this->resolution_due_at->isPast(),
        };

        if ($firstResponseMissed || $resolutionMissed) {
            return 'breached';
        }

        if ($this->isClosed()) {
            return 'met';
        }

        $next = $this->first_responded_at === null ? $this->first_response_due_at : $this->resolution_due_at;

        return $next !== null && $next->lessThanOrEqualTo(now()->addHour()) ? 'due_soon' : 'on_track';
    }

    /** @param Builder<Ticket> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('status', ['resolved', 'closed']);
    }

    /** Open tickets that have already missed a first-response or resolution deadline. */
    public function scopeBreached(Builder $query): void
    {
        $query->open()->where(function (Builder $q): void {
            $q->where(fn (Builder $r) => $r->whereNull('first_responded_at')->where('first_response_due_at', '<', now()))
                ->orWhere('resolution_due_at', '<', now());
        });
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'related');
    }
}
