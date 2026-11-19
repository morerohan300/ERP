<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One contact's delivery state within a campaign.
 *
 * @property int $id
 * @property int $campaign_id
 * @property int $contact_id
 * @property string $status
 */
#[Fillable(['campaign_id', 'contact_id', 'status', 'sent_at', 'opened_at', 'clicked_at', 'bounced_at', 'error'])]
class CampaignRecipient extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'bounced_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
