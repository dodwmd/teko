<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Monitoring;

use App\Orchid\Filters\Monitoring\DateRangeFilter;
use App\Orchid\Filters\Monitoring\LevelFilter;
use App\Orchid\Filters\Monitoring\MessageFilter;
use App\Orchid\Filters\Monitoring\TypeFilter;
use Orchid\Screen\Layouts\Selection;

class ErrorFiltersLayout extends Selection
{
    /**
     * Filters to be displayed
     *
     * @return string[]
     *
     * @psalm-return list{MessageFilter::class, TypeFilter::class, LevelFilter::class, DateRangeFilter::class}
     */
    public function filters(): array
    {
        return [
            MessageFilter::class,
            TypeFilter::class,
            LevelFilter::class,
            DateRangeFilter::class,
        ];
    }
}
