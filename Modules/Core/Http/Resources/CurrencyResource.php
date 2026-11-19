<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Models\Currency;

/** @mixin Currency */
final class CurrencyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->code,
            'code' => $this->code,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'decimals' => $this->decimals,
        ];
    }
}
