<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Models\Branch;

/** @mixin Branch */
final class BranchResource extends UuidResource
{
    /** @return array<string, mixed> */
    protected function payload(Request $request): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'is_head_office' => $this->is_head_office,
            'is_active' => $this->is_active,
        ];
    }
}
