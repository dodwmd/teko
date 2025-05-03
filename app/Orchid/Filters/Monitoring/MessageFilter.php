<?php

namespace App\Orchid\Filters\Monitoring;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Input;

class MessageFilter extends Filter
{
    public function name(): string
    {
        return 'message';
    }

    public function parameters(): array
    {
        return ['message'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('message', 'like', '%'.$this->request->get('message').'%');
    }

    public function display(): iterable
    {
        return [
            Input::make('message')
                ->type('text')
                ->placeholder('Search by error message')
                ->value($this->request->get('message')),
        ];
    }
}
