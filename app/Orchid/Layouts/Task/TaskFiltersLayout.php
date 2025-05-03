<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Task;

use App\Orchid\Filters\Task\RepositoryFilter;
use App\Orchid\Filters\Task\StatusFilter;
use App\Orchid\Filters\Task\TitleFilter;
use App\Orchid\Filters\Task\TypeFilter;
use Orchid\Screen\Layouts\Selection;

class TaskFiltersLayout extends Selection
{
    /**
     * The filterable data.
     *
     * @return array
     */
    public function filters(): iterable
    {
        return [
            TitleFilter::class,
            StatusFilter::class,
            TypeFilter::class,
            RepositoryFilter::class,
        ];
    }
}
