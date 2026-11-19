<?php

declare(strict_types=1);

namespace Modules\Core\Repositories;

use App\Support\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Models\Branch;

/**
 * @extends BaseRepository<Branch>
 */
class BranchRepository extends BaseRepository
{
    protected string $model = Branch::class;

    protected array $searchable = ['name', 'code', 'email', 'phone'];

    protected array $sortable = ['name', 'code', 'created_at'];

    protected string $defaultSort = 'name';

    protected array $exactFilters = ['is_active'];

    /** @return Builder<Branch> */
    public function query(): Builder
    {
        return parent::query()->withCount('departments');
    }
}
