<?php

declare(strict_types=1);

namespace Modules\Core\Repositories;

use App\Models\User;
use App\Support\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

/**
 * Users of the current company.
 *
 * @extends BaseRepository<User>
 */
class UserRepository extends BaseRepository
{
    protected string $model = User::class;

    protected array $searchable = ['name', 'email', 'phone'];

    protected array $sortable = ['name', 'email', 'created_at', 'last_login_at'];

    protected string $defaultSort = 'name';

    protected array $with = ['roles'];

    /** @return Builder<User> */
    public function query(): Builder
    {
        return parent::query()->inCurrentCompany();
    }

    /** @param Builder<User> $query */
    protected function filterRole(Builder $query, string $role): void
    {
        $query->whereHas('roles', fn (Builder $q) => $q->where('name', $role));
    }

    /** @param Builder<User> $query */
    protected function filterStatus(Builder $query, string $status): void
    {
        $query->where('is_active', $status === 'active');
    }
}
