<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Model */
final class ActivityResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event' => $this->event,
            'description' => $this->description,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'causer_id' => $this->causer_id,
            'properties' => $this->properties,
            'created_at' => $this->created_at?->toJSON(),
        ];
    }
}
