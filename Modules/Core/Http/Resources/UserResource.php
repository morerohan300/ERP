<?php

declare(strict_types=1);

namespace Modules\Core\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;

/** @mixin User */
final class UserResource extends UuidResource
{
    /** @return array<string, mixed> */
    protected function payload(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatarUrl(),
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'is_active' => $this->is_active,
            'two_factor' => $this->hasTwoFactorEnabled(),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->values()),
            'created_at' => $this->created_at?->toJSON(),
        ];
    }
}
