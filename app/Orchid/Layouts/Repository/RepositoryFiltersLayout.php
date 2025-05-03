<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Repository;

use App\Orchid\Filters\Repository\LanguageFilter;
use App\Orchid\Filters\Repository\NameFilter;
use App\Orchid\Filters\Repository\ProviderFilter;
use Orchid\Screen\Layouts\Selection;

class RepositoryFiltersLayout extends Selection
{
    /**
     * The filterable data.
     *
     * @return array
     */
    public function filters(): iterable
    {
        return [
            NameFilter::class,
            LanguageFilter::class,
            ProviderFilter::class,
        ];
    }
}
