<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Public UUID alongside the internal BIGINT key. Routes and the API bind on
 * `uuid` so internal IDs are never exposed.
 *
 * @mixin Model
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid7();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
