<?php

namespace App\Repositories;

use App\Contracts\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all resources
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /**
     * Find resource by id
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->select($columns)->find($id);
    }

    /**
     * Find by field value
     */
    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->select($columns)->where($field, $value)->first();
    }

    /**
     * Create a new resource
     */
    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    /**
     * Update resource
     */
    public function update(int $id, array $attributes): bool
    {
        $model = $this->find($id);

        if (! $model) {
            return false;
        }

        return $model->update($attributes);
    }

    /**
     * Delete resource
     */
    public function delete(int $id): bool
    {
        $model = $this->find($id);

        if (! $model) {
            return false;
        }

        // Ensure we always return a boolean value
        return $model->delete() ? true : false;
    }
}
