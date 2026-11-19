<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\ApprovalRequest;

/**
 * A document entered (or advanced to the next step of) an approval workflow.
 */
final class ApprovalRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly ApprovalRequest $request) {}
}
