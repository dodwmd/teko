<?php

namespace App\Orchid\Filters\Task;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Input;

class TitleFilter extends Filter
{
    public function name(): string
    {
        return 'title';
    }

    public function parameters(): array
    {
        return ['title'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('title', 'like', '%'.$this->request->get('title').'%');
    }

    public function display(): iterable
    {
        return [
            Input::make('title')
                ->type('text')
                ->placeholder('Search by title')
                ->value($this->request->get('title')),
        ];
    }
}
