<?php

declare(strict_types=1);

namespace App\Support\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface RepositoryInterface
{
    /** @return Builder<TModel> */
    public function query(): Builder;

    /** @return TModel|null */
    public function find(int|string $id): ?Model;

    /** @return TModel */
    public function findOrFail(int|string $id): Model;

    /** @return TModel */
    public function findByUuid(string $uuid): Model;

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, TModel>
     */
    public function all(array $filters = []): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function create(array $attributes): Model;

    /**
     * @param  TModel  $model
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function update(Model $model, array $attributes): Model;

    /** @param TModel $model */
    public function delete(Model $model): void;
}
