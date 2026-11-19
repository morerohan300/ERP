<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Per-company counter for one document type. Only touched through
 * App\Support\Numbering\DocumentNumberGenerator (row-locked).
 *
 * @property string $document_type
 * @property string $prefix
 * @property string $format
 * @property int $next_number
 * @property int $padding
 * @property string $reset_period
 * @property Carbon|null $last_reset_at
 */
#[Fillable(['company_id', 'document_type', 'prefix', 'format', 'next_number', 'padding', 'reset_period', 'last_reset_at'])]
class NumberSequence extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'next_number' => 'integer',
            'padding' => 'integer',
            'last_reset_at' => 'datetime',
        ];
    }

    public function label(): string
    {
        return str($this->document_type)->replace('_', ' ')->title()->toString();
    }
}
