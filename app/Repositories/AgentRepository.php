<?php

namespace App\Repositories;

use App\Contracts\Repositories\AgentRepositoryInterface;
use App\Models\Agent;
use Illuminate\Support\Collection;

class AgentRepository extends BaseRepository implements AgentRepositoryInterface
{
    /**
     * AgentRepository constructor.
     */
    public function __construct(Agent $model)
    {
        parent::__construct($model);
    }

    /**
     * Get agents by type
     */
    public function getByType(string $type): Collection
    {
        return $this->model->where('type', $type)->get();
    }

    /**
     * Get agents by language
     */
    public function getByLanguage(string $language): Collection
    {
        return $this->model->where('language', $language)->get();
    }

    /**
     * Get enabled agents
     */
    public function getEnabled(): Collection
    {
        return $this->model->where('enabled', true)->get();
    }

    /**
     * Get agents by capability
     */
    public function getByCapability(string $capability): Collection
    {
        return $this->model->whereJsonContains('capabilities', $capability)->get();
    }
}
