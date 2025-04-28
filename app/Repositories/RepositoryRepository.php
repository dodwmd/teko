<?php

namespace App\Repositories;

use App\Contracts\Repositories\RepositoryRepositoryInterface;
use App\Models\Repository;
use Illuminate\Support\Collection;

class RepositoryRepository extends BaseRepository implements RepositoryRepositoryInterface
{
    /**
     * RepositoryRepository constructor.
     */
    public function __construct(Repository $model)
    {
        parent::__construct($model);
    }

    /**
     * Get repositories by provider
     */
    public function getByProvider(string $provider): Collection
    {
        return $this->model->where('provider', $provider)->get();
    }

    /**
     * Get repositories by language
     */
    public function getByLanguage(string $language): Collection
    {
        return $this->model->where('language', $language)->get();
    }

    /**
     * Get active repositories
     */
    public function getActive(): Collection
    {
        return $this->model->where('active', true)->get();
    }
}
