<?php

declare(strict_types=1);

namespace Modules\CRM\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CRM\Models\Deal;

/** Fired when a deal is marked as lost. */
final class DealLost
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Deal $deal) {}
}
