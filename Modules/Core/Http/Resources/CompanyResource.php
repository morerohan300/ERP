<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Modules\Core\Models\Company;

/** @mixin Company */
final class CompanyResource extends UuidResource
{
    /** @return array<string, mixed> */
    protected function payload(Request $request): array
    {
        return [
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'base_currency' => $this->base_currency_code,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'is_active' => $this->is_active,
        ];
    }
}
