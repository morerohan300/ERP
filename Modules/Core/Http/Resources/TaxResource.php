<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Models\Tax;

/** @mixin Tax */
final class TaxResource extends UuidResource
{
    /** @return array<string, mixed> */
    protected function payload(Request $request): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'rate' => $this->rate,
            'type' => $this->type,
            'scope' => $this->scope,
            'is_compound' => $this->is_compound,
            'is_inclusive' => $this->is_inclusive,
            'is_active' => $this->is_active,
        ];
    }
}
