<?php

namespace App\Contracts\Repositories;

use Illuminate\Support\Collection;

interface TaskRepositoryInterface extends RepositoryInterface
{
    /**
     * Get tasks by repository
     */
    public function getByRepository(int $repositoryId): Collection;

    /**
     * Get tasks by status
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get tasks by type
     */
    public function getByType(string $type): Collection;

    /**
     * Get tasks by provider
     */
    public function getByProvider(string $provider): Collection;
}
