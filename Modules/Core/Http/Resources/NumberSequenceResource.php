<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Models\NumberSequence;

/** @mixin NumberSequence */
final class NumberSequenceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'document_type' => $this->document_type,
            'prefix' => $this->prefix,
            'format' => $this->format,
            'next_number' => $this->next_number,
            'padding' => $this->padding,
            'reset_period' => $this->reset_period,
        ];
    }
}
