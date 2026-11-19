<?php

declare(strict_types=1);

namespace App\Support\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Maintains created_by / updated_by from the authenticated user.
 *
 * @mixin Model
 */
trait HasAuditColumns
{
    public static function bootHasAuditColumns(): void
    {
        static::creating(function ($model): void {
            $userId = Auth::id();
            if ($userId !== null) {
                $model->created_by ??= $userId;
                $model->updated_by ??= $userId;
            }
        });

        static::updating(function ($model): void {
            $userId = Auth::id();
            if ($userId !== null) {
                $model->updated_by = $userId;
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
