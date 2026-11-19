<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Raw key/value setting. Read and write through Modules\Core\Services\SettingsService
 * which handles JSON encoding, encryption and caching.
 *
 * Deliberately NOT tenant-scoped via global scope: company_id may be null
 * for global settings, and the service always filters explicitly.
 */
#[Fillable(['company_id', 'group', 'key', 'value', 'is_encrypted'])]
class Setting extends Model
{
    protected function casts(): array
    {
        return ['is_encrypted' => 'boolean'];
    }
}
