<?php

declare(strict_types=1);

namespace App\Support\Repositories;

use App\Support\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent repository base. Subclasses declare the model, searchable
 * columns, sortable columns, default eager loads and custom filters.
 *
 * Supported filters: search, sort (prefix "-" for desc), plus any key that
 * has an `filterXxx(Builder $q, mixed $value)` method on the subclass, or
 * appears in $exactFilters.
 *
 * @template TModel of Model
 *
 * @implements RepositoryInterface<TModel>
 */
abstract class BaseRepository implements RepositoryInterface
{
    /** @var class-string<TModel> */
    protected string $model;

    /** @var array<int, string> columns matched with LIKE for "search" */
    protected array $searchable = [];

    /** @var array<int, string> columns that may be sorted on */
    protected array $sortable = ['created_at'];

    protected string $defaultSort = '-created_at';

    /** @var array<int, string> */
    protected array $with = [];

    /** @var array<int, string> filters applied as where(column, value) */
    protected array $exactFilters = [];

    /** @return Builder<TModel> */
    public function query(): Builder
    {
        return $this->model::query()->with($this->with);
    }

    public function find(int|string $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function findByUuid(string $uuid): Model
    {
        return $this->query()->where('uuid', $uuid)->firstOrFail();
    }

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->filtered($filters)->paginate(min(max($perPage, 1), 200))->withQueryString();
    }

    public function all(array $filters = []): Collection
    {
        return $this->filtered($filters)->get();
    }

    public function create(array $attributes): Model
    {
        return $this->model::query()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes)->save();

        return $model->refresh();
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<TModel>
     */
    public function filtered(array $filters): Builder
    {
        $query = $this->query();

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '' && $this->searchable !== []) {
            $query->where(function (Builder $q) use ($search): void {
                foreach ($this->searchable as $column) {
                    $q->orWhere($column, 'like', '%'.addcslashes($search, '%_\\').'%');
                }
            });
        }

        foreach ($filters as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $method = 'filter'.str($key)->studly();
            if (method_exists($this, $method)) {
                $this->{$method}($query, $value);
            } elseif (in_array($key, $this->exactFilters, true)) {
                is_array($value) ? $query->whereIn($key, $value) : $query->where($key, $value);
            }
        }

        $this->applySort($query, (string) ($filters['sort'] ?? $this->defaultSort));

        return $query;
    }

    /** @param Builder<TModel> $query */
    protected function applySort(Builder $query, string $sort): void
    {
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        if (in_array($column, $this->sortable, true)) {
            $query->orderBy($query->getModel()->qualifyColumn($column), $direction);
        }
    }
}
