<?php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\ApprovalRequest;

/**
 * A workflow finished. `$request->status` is "approved" or "rejected".
 * Owning modules listen to move the document to its next state.
 */
final class ApprovalCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly ApprovalRequest $request) {}

    public function approved(): bool
    {
        return $this->request->status === 'approved';
    }
}
