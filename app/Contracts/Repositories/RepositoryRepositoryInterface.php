<?php

namespace App\Contracts\Repositories;

use Illuminate\Support\Collection;

interface RepositoryRepositoryInterface extends RepositoryInterface
{
    /**
     * Get repositories by provider
     */
    public function getByProvider(string $provider): Collection;

    /**
     * Get repositories by language
     */
    public function getByLanguage(string $language): Collection;

    /**
     * Get active repositories
     */
    public function getActive(): Collection;
}
