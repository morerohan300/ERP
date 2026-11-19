<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A user or contact who takes part in an activity.
 *
 * @property int $activity_id
 */
#[Fillable(['activity_id', 'participant_type', 'participant_id'])]
class ActivityParticipant extends Model
{
    use BelongsToCompany;

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function participant(): MorphTo
    {
        return $this->morphTo();
    }
}
