<?php

namespace App\Orchid\Filters\Task;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Fields\Select;

class StatusFilter extends Filter
{
    public function name(): string
    {
        return 'status';
    }

    public function parameters(): array
    {
        return ['status'];
    }

    public function run(Builder $builder): Builder
    {
        return $builder->where('status', $this->request->get('status'));
    }

    public function display(): iterable
    {
        return [
            Select::make('status')
                ->empty('All statuses')
                ->options([
                    'pending' => 'Pending',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'failed' => 'Failed',
                    'cancelled' => 'Cancelled',
                ])
                ->value($this->request->get('status')),
        ];
    }
}
