<?php

declare(strict_types=1);

namespace Modules\Core\Repositories;

use App\Support\Repositories\BaseRepository;
use Modules\Core\Models\Department;

/**
 * @extends BaseRepository<Department>
 */
class DepartmentRepository extends BaseRepository
{
    protected string $model = Department::class;

    protected array $searchable = ['name', 'code'];

    protected array $sortable = ['name', 'code', 'created_at'];

    protected string $defaultSort = 'name';

    protected array $with = ['branch', 'manager', 'parent'];

    protected array $exactFilters = ['branch_id', 'parent_id'];
}
