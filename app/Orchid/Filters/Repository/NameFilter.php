<?php

namespace App\Orchid\Filters\Repository;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Input;

class NameFilter extends Filter
{
    public function name(): string
    {
        return 'name';
    }

    public function parameters(): array
    {
        return ['name'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('name', 'like', '%'.$this->request->get('name').'%');
    }

    public function display(): iterable
    {
        return [
            Input::make('name')
                ->type('text')
                ->placeholder('Search by name')
                ->value($this->request->get('name')),
        ];
    }
}
