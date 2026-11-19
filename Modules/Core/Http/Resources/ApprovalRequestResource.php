<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Models\ApprovalRequest;

/** @mixin ApprovalRequest */
final class ApprovalRequestResource extends UuidResource
{
    /** @return array<string, mixed> */
    protected function payload(Request $request): array
    {
        return [
            'number' => $this->number,
            'workflow' => $this->whenLoaded('workflow', fn () => ['id' => $this->workflow->uuid, 'name' => $this->workflow->name]),
            'document_type' => $this->whenLoaded('approvable', fn () => $this->approvable?->getMorphClass()),
            'current_step' => $this->current_step,
            'status' => $this->status,
            'requester' => $this->whenLoaded('requester', fn () => ['id' => $this->requester?->uuid, 'name' => $this->requester?->name]),
            'completed_at' => $this->completed_at?->toJSON(),
            'created_at' => $this->created_at?->toJSON(),
        ];
    }
}
