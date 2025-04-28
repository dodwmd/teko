<?php

namespace App\Contracts\Repositories;

use Illuminate\Support\Collection;

interface AgentRepositoryInterface extends RepositoryInterface
{
    /**
     * Get agents by type
     */
    public function getByType(string $type): Collection;

    /**
     * Get agents by language
     */
    public function getByLanguage(string $language): Collection;

    /**
     * Get enabled agents
     */
    public function getEnabled(): Collection;

    /**
     * Get agents by capability
     */
    public function getByCapability(string $capability): Collection;
}
