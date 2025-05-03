<?php

namespace App\Orchid\Filters\Monitoring;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

class TypeFilter extends Filter
{
    public function name(): string
    {
        return 'type';
    }

    public function parameters(): array
    {
        return ['type'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('type', $this->request->get('type'));
    }

    public function display(): iterable
    {
        return [
            Select::make('type')
                ->empty('All types')
                ->options([
                    'exception' => 'Exception',
                    'error' => 'Error',
                    'warning' => 'Warning',
                    'notice' => 'Notice',
                    'info' => 'Info',
                ])
                ->value($this->request->get('type')),
        ];
    }
}
