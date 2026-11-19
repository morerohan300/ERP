<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Concerns\LogsModelActivity;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Database\Factories\CampaignFactory;

/**
 * An email or SMS blast to a segment of contacts.
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $status
 * @property ?array<string, int> $stats
 */
#[Fillable(['name', 'type', 'segment_id', 'email_template_id', 'sms_body', 'scheduled_at', 'budget'])]
class Campaign extends Model
{
    /** @use HasFactory<CampaignFactory> */
    use BelongsToCompany, HasAuditColumns, HasFactory, HasUuid, LogsModelActivity, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_FAILED = 'failed';

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'budget' => 'decimal:4',
            'stats' => 'array',
        ];
    }

    protected static function newFactory(): CampaignFactory
    {
        return CampaignFactory::new();
    }

    /** A campaign can only be edited or deleted before it starts sending. */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED], true);
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }
}
