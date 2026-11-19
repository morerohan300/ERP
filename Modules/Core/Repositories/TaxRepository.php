<?php

declare(strict_types=1);

namespace Modules\Core\Repositories;

use App\Support\Repositories\BaseRepository;
use Modules\Core\Models\Tax;

/**
 * @extends BaseRepository<Tax>
 */
class TaxRepository extends BaseRepository
{
    protected string $model = Tax::class;

    protected array $searchable = ['name', 'code'];

    protected array $sortable = ['name', 'code', 'rate', 'created_at'];

    protected string $defaultSort = 'name';

    protected array $exactFilters = ['scope', 'type', 'is_active'];
}
