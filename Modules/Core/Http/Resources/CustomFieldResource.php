<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Models\CustomField;

/** @mixin CustomField */
final class CustomFieldResource extends UuidResource
{
    /** @return array<string, mixed> */
    protected function payload(Request $request): array
    {
        return [
            'entity_type' => $this->entity_type,
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'options' => $this->options,
            'is_required' => $this->is_required,
            'show_in_table' => $this->show_in_table,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
