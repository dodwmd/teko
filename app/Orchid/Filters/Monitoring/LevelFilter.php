<?php

namespace App\Orchid\Filters\Monitoring;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

class LevelFilter extends Filter
{
    public function name(): string
    {
        return 'level';
    }

    public function parameters(): array
    {
        return ['level'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('level', $this->request->get('level'));
    }

    public function display(): iterable
    {
        return [
            Select::make('level')
                ->empty('All levels')
                ->options([
                    'emergency' => 'Emergency',
                    'alert' => 'Alert',
                    'critical' => 'Critical',
                    'error' => 'Error',
                    'warning' => 'Warning',
                    'notice' => 'Notice',
                    'info' => 'Info',
                    'debug' => 'Debug',
                ])
                ->value($this->request->get('level')),
        ];
    }
}
