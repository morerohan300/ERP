<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A record of every email the CRM sends, linked to the lead or contact it went to.
 *
 * @property int $id
 * @property string $to_address
 * @property string $subject
 * @property string $status
 */
#[Fillable([
    'emailable_type', 'emailable_id', 'email_template_id', 'from_address', 'to_address', 'cc_address',
    'subject', 'body', 'status', 'sent_at', 'opened_at', 'error',
])]
class EmailLog extends Model
{
    use BelongsToCompany, HasAuditColumns, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
        ];
    }

    public function emailable(): MorphTo
    {
        return $this->morphTo();
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
}
