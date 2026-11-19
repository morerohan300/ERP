<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Models\Department;

/** @mixin Department */
final class DepartmentResource extends UuidResource
{
    /** @return array<string, mixed> */
    protected function payload(Request $request): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'branch_id' => $this->branch_id,
            'parent_id' => $this->parent_id,
            'manager_id' => $this->manager_id,
            'description' => $this->description,
        ];
    }
}
