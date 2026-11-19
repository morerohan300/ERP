<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A queued Excel/CSV import or export job and its outcome.
 *
 * @property string $direction import|export
 * @property string $status queued|processing|completed|failed
 * @property array<int, string>|null $errors
 */
#[Fillable([
    'direction', 'module', 'entity', 'format', 'file_path', 'original_name', 'status',
    'total_rows', 'processed_rows', 'failed_rows', 'errors', 'filters', 'completed_at',
])]
class DataTransfer extends Model
{
    use BelongsToCompany, HasAuditColumns, HasUuid;

    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'filters' => 'array',
            'completed_at' => 'datetime',
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'failed_rows' => 'integer',
        ];
    }

    public function progressPercent(): int
    {
        return $this->total_rows > 0 ? (int) floor(($this->processed_rows / $this->total_rows) * 100) : ($this->status === 'completed' ? 100 : 0);
    }
}
