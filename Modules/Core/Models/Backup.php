<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Models\User;
use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

/**
 * Full-database backup archive (system level — not tenant-scoped; only
 * company owners with core.backup.* permission may manage backups).
 */
#[Fillable(['disk', 'path', 'size', 'status', 'error', 'created_by', 'completed_at'])]
class Backup extends Model
{
    use HasUuid;

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size);
    }
}
