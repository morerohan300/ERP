<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Global ISO-4217 currency (not tenant-scoped).
 *
 * @property string $code
 * @property string $name
 * @property string $symbol
 * @property int $decimals
 */
#[Fillable(['code', 'name', 'symbol', 'decimals'])]
class Currency extends Model
{
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return ['decimals' => 'integer'];
    }

    public function label(): string
    {
        return "{$this->code} — {$this->name}";
    }
}
