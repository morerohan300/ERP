<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Models\User;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A message on a ticket from staff (user_id) or the customer (contact_id).
 * Internal replies are visible to staff only.
 *
 * @property int $ticket_id
 * @property ?int $user_id
 * @property ?int $contact_id
 * @property string $body
 * @property bool $is_internal
 */
#[Fillable(['ticket_id', 'user_id', 'contact_id', 'body', 'is_internal'])]
class TicketReply extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return ['is_internal' => 'boolean'];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /** Name shown next to the reply. */
    public function authorName(): string
    {
        return $this->user?->name ?? $this->contact?->full_name ?? __('Customer');
    }
}
