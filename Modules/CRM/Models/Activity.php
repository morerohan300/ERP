<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Models\User;
use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\ActivityFactory;

/**
 * A call, meeting, task, note or email tied to any CRM record through the
 * `related` morph (lead, contact, crm_account, deal, ticket).
 *
 * @property int $id
 * @property string $type
 * @property string $subject
 * @property ?\Illuminate\Support\Carbon $due_at
 * @property ?\Illuminate\Support\Carbon $starts_at
 * @property ?\Illuminate\Support\Carbon $completed_at
 */
#[Fillable([
    'type', 'subject', 'description', 'related_type', 'related_id', 'owner_id', 'due_at', 'starts_at', 'ends_at',
    'location', 'outcome', 'priority', 'reminder_at',
])]
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    public const TYPES = ['call' => 'Call', 'meeting' => 'Meeting', 'task' => 'Task', 'note' => 'Note', 'email' => 'Email'];

    public const PRIORITIES = ['low' => 'Low', 'normal' => 'Normal', 'high' => 'High'];

    /** Morph aliases an activity can be attached to. */
    public const RELATABLE = ['lead', 'contact', 'crm_account', 'deal', 'ticket'];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'completed_at' => 'datetime',
            'reminder_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    protected static function newFactory(): ActivityFactory
    {
        return ActivityFactory::new();
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /** The moment the activity is scheduled for (meetings use starts_at, tasks use due_at). */
    public function scheduledAt(): ?\Illuminate\Support\Carbon
    {
        return $this->starts_at ?? $this->due_at;
    }

    public function isOverdue(): bool
    {
        $when = $this->due_at ?? $this->starts_at;

        return ! $this->isCompleted() && $when !== null && $when->isPast();
    }

    /** @param Builder<Activity> $query */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNull('completed_at');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ActivityParticipant::class);
    }
}
