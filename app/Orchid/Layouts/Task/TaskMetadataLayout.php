<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Task;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Code;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Layouts\Rows;

class TaskMetadataLayout extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [
            DateTimer::make('task.started_at')
                ->title('Started At')
                ->format('Y-m-d H:i:s')
                ->allowInput()
                ->help('When the task was started'),

            DateTimer::make('task.completed_at')
                ->title('Completed At')
                ->format('Y-m-d H:i:s')
                ->allowInput()
                ->help('When the task was completed'),

            Code::make('task.metadata')
                ->title('Metadata')
                ->language('json')
                ->lineNumbers()
                ->value(function ($task) {
                    return json_encode($task->metadata ?? [], JSON_PRETTY_PRINT);
                })
                ->help('Additional task metadata in JSON format'),
        ];
    }
}
