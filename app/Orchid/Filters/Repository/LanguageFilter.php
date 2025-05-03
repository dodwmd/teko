<?php

namespace App\Orchid\Filters\Repository;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

class LanguageFilter extends Filter
{
    public function name(): string
    {
        return 'language';
    }

    public function parameters(): array
    {
        return ['language'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('language', $this->request->get('language'));
    }

    public function display(): iterable
    {
        return [
            Select::make('language')
                ->empty('All languages')
                ->options([
                    'php' => 'PHP',
                    'python' => 'Python',
                    'javascript' => 'JavaScript',
                    'typescript' => 'TypeScript',
                    'ruby' => 'Ruby',
                    'go' => 'Go',
                    'java' => 'Java',
                    'csharp' => 'C#',
                    'other' => 'Other',
                ])
                ->value($this->request->get('language')),
        ];
    }
}
