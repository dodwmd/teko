<?php

namespace App\Orchid\Filters\Task;

use App\Models\Repository;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

class RepositoryFilter extends Filter
{
    public function name(): string
    {
        return 'repository_id';
    }

    public function parameters(): array
    {
        return ['repository_id'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('repository_id', $this->request->get('repository_id'));
    }

    public function display(): iterable
    {
        return [
            Select::make('repository_id')
                ->fromModel(Repository::class, 'name', 'id')
                ->empty('All repositories')
                ->value($this->request->get('repository_id')),
        ];
    }
}
