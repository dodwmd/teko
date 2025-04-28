<?php

namespace App\Repositories;

use App\Contracts\Repositories\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Support\Collection;

class TaskRepository extends BaseRepository implements TaskRepositoryInterface
{
    /**
     * TaskRepository constructor.
     */
    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    /**
     * Get tasks by repository
     */
    public function getByRepository(int $repositoryId): Collection
    {
        return $this->model->where('repository_id', $repositoryId)->get();
    }

    /**
     * Get tasks by status
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Get tasks by type
     */
    public function getByType(string $type): Collection
    {
        return $this->model->where('type', $type)->get();
    }

    /**
     * Get tasks by provider
     */
    public function getByProvider(string $provider): Collection
    {
        return $this->model->where('provider', $provider)->get();
    }
}
