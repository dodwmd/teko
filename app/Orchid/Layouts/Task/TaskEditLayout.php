<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Task;

use App\Models\Repository;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class TaskEditLayout extends Rows
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
            Input::make('task.title')
                ->title('Title')
                ->placeholder('Task title')
                ->required(),

            TextArea::make('task.description')
                ->title('Description')
                ->rows(5)
                ->placeholder('Task description'),

            Select::make('task.repository_id')
                ->title('Repository')
                ->fromModel(Repository::class, 'name')
                ->empty('-- Select Repository --')
                ->help('Select the repository this task is associated with'),

            Select::make('task.type')
                ->title('Type')
                ->options([
                    'implementation' => 'Implementation',
                    'review' => 'Code Review',
                    'refinement' => 'Story Refinement',
                    'documentation' => 'Documentation',
                    'testing' => 'Testing',
                ])
                ->required()
                ->help('Type of task to be performed'),

            Select::make('task.status')
                ->title('Status')
                ->options([
                    'pending' => 'Pending',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'failed' => 'Failed',
                    'cancelled' => 'Cancelled',
                ])
                ->required()
                ->help('Current status of the task'),

            Input::make('task.provider')
                ->title('Provider')
                ->placeholder('e.g., GitHub, Jira')
                ->help('Source system where the task originated'),

            Input::make('task.external_id')
                ->title('External ID')
                ->placeholder('e.g., JIRA-123, #456')
                ->help('ID of the task in the external system'),

            Input::make('task.external_url')
                ->title('External URL')
                ->type('url')
                ->placeholder('https://...')
                ->help('Link to the task in the external system'),

            Input::make('task.branch_name')
                ->title('Branch Name')
                ->placeholder('e.g., feature/implement-login')
                ->help('Branch where the task is being implemented'),

            Input::make('task.pull_request_url')
                ->title('Pull Request URL')
                ->type('url')
                ->placeholder('https://github.com/...')
                ->help('Link to the pull request for this task'),
        ];
    }
}
