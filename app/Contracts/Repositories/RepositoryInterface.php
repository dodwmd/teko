<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    /**
     * Get all resources
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find resource by id
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find by field value
     */
    public function findBy(string $field, mixed $value, array $columns = ['*']): ?Model;

    /**
     * Create a new resource
     */
    public function create(array $attributes): Model;

    /**
     * Update resource
     */
    public function update(int $id, array $attributes): bool;

    /**
     * Delete resource
     */
    public function delete(int $id): bool;
}
