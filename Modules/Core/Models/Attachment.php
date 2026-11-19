<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Number;

#[Fillable(['attachable_type', 'attachable_id', 'disk', 'path', 'original_name', 'mime_type', 'size'])]
class Attachment extends Model
{
    use BelongsToCompany, HasAuditColumns, HasUuid, SoftDeletes;

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size);
    }
}
