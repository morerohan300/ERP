<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-company on/off switch for a module (composite PK company_id + module).
 */
#[Fillable(['company_id', 'module', 'is_enabled', 'settings'])]
class CompanyModule extends Model
{
    protected $primaryKey = 'module';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Composite key: always constrain saves/deletes by company AND module so
     * an update can never touch another company's row.
     *
     * @param  Builder<CompanyModule>  $query
     * @return Builder<CompanyModule>
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('company_id', $this->getOriginal('company_id') ?? $this->company_id)
            ->where('module', $this->getOriginal('module') ?? $this->module);
    }
}
