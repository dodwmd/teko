<?php

namespace App\Orchid\Filters\Monitoring;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\DateRange;

class DateRangeFilter extends Filter
{
    public function name(): string
    {
        return 'created_at';
    }

    public function parameters(): array
    {
        return ['created_at'];
    }

    public function run(Builder $builder): Builder
    {
        $dates = $this->request->get('created_at');

        return $builder->when($dates, function (Builder $query) use ($dates) {
            $start = null;
            $end = null;

            if (isset($dates['start'])) {
                $start = date('Y-m-d', strtotime($dates['start']));
            }

            if (isset($dates['end'])) {
                $end = date('Y-m-d', strtotime($dates['end']));
            }

            if ($start && $end) {
                return $query->whereBetween('created_at', [$start, $end]);
            }

            return $query;
        });
    }

    public function display(): iterable
    {
        return [
            DateRange::make('created_at')
                ->placeholder('Filter by date range')
                ->value($this->request->get('created_at')),
        ];
    }
}
