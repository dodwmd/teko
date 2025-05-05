<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Task;

use App\Models\Task;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TaskListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'tasks';

    /**
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('checkbox', '')
                ->align(TD::ALIGN_CENTER)
                ->width('1%')
                ->render(function (Task $task) {
                    return '<input type="checkbox" 
                                class="form-check-input" 
                                name="id[]" 
                                value="'.$task->id.'" 
                                form="task-actions-form">';
                }),

            TD::make('title', 'Title')
                ->sort()
                ->filter()
                ->render(function (Task $task) {
                    return Link::make($task->title)
                        ->route('platform.task.edit', $task->id);
                }),

            TD::make('repository.name', 'Repository')
                ->sort()
                ->render(function (Task $task) {
                    if ($task->repository) {
                        return $task->repository->name;
                    }

                    return '—';
                }),

            TD::make('type', 'Type')
                ->sort()
                ->render(function (Task $task) {
                    $types = [
                        'implementation' => 'Implementation',
                        'review' => 'Code Review',
                        'refinement' => 'Story Refinement',
                        'documentation' => 'Documentation',
                        'testing' => 'Testing',
                    ];

                    return $types[$task->type] ?? ucfirst($task->type);
                }),

            TD::make('status', 'Status')
                ->sort()
                ->render(function (Task $task) {
                    $statusClasses = [
                        'pending' => 'bg-warning',
                        'in_progress' => 'bg-info',
                        'completed' => 'bg-success',
                        'failed' => 'bg-danger',
                        'cancelled' => 'bg-secondary',
                    ];
                    $class = $statusClasses[$task->status] ?? 'bg-light';

                    return '<span class="badge '.$class.'">'.ucfirst(str_replace('_', ' ', $task->status)).'</span>';
                }),

            TD::make('created_at', 'Created')
                ->sort()
                ->render(function (Task $task) {
                    return $task->created_at->format('Y-m-d H:i');
                }),

            TD::make('actions', 'Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (Task $task) {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            Link::make('View Details')
                                ->icon('eye')
                                ->route('platform.task.edit', $task->id),

                            Button::make('Cancel')
                                ->icon('close')
                                ->method('cancelSelected', ['id' => [$task->id]])
                                ->confirm('Are you sure you want to cancel this task?')
                                ->canSee(in_array($task->status, ['pending', 'in_progress'])),

                            Button::make('Delete')
                                ->icon('trash')
                                ->method('remove', ['task' => $task->id])
                                ->confirm('Are you sure you want to delete this task?'),
                        ]);
                }),
        ];
    }
}
