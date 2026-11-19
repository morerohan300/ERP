<?php

declare(strict_types=1);

namespace Modules\CRM\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CRM\Models\Deal;

/**
 * Fired when a deal is won. The Sales module listens to this event to make sure a
 * customer exists and to draft a sales order from the deal's products.
 */
final class DealWon
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Deal $deal) {}
}
