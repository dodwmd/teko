<?php

namespace App\Orchid\Filters\Repository;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

class ProviderFilter extends Filter
{
    public function name(): string
    {
        return 'provider';
    }

    public function parameters(): array
    {
        return ['provider'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('provider', $this->request->get('provider'));
    }

    public function display(): iterable
    {
        return [
            Select::make('provider')
                ->empty('All providers')
                ->options([
                    'github' => 'GitHub',
                    'gitlab' => 'GitLab',
                    'bitbucket' => 'Bitbucket',
                    'azure' => 'Azure DevOps',
                    'local' => 'Local',
                    'other' => 'Other',
                ])
                ->value($this->request->get('provider')),
        ];
    }
}
