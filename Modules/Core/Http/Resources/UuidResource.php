<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resources expose the public UUID as `id`, never the internal key.
 */
abstract class UuidResource extends JsonResource
{
    /** @return array<string, mixed> */
    abstract protected function payload(Request $request): array;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return ['id' => $this->uuid] + $this->payload($request);
    }
}
