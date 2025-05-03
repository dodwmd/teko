<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Repository;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class RepositoryEditLayout extends Rows
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
            Input::make('repository.name')
                ->title('Repository Name')
                ->placeholder('Enter repository name')
                ->required(),

            Input::make('repository.url')
                ->title('Repository URL')
                ->type('url')
                ->placeholder('https://github.com/username/repo')
                ->required()
                ->help('The URL of the Git repository'),

            Select::make('repository.provider')
                ->title('Provider')
                ->options([
                    'github' => 'GitHub',
                    'gitlab' => 'GitLab',
                    'bitbucket' => 'Bitbucket',
                    'azure' => 'Azure DevOps',
                ])
                ->required()
                ->help('The Git provider hosting this repository'),

            Input::make('repository.default_branch')
                ->title('Default Branch')
                ->placeholder('main')
                ->required()
                ->value('main')
                ->help('The default branch of the repository (e.g., main, master)'),

            Select::make('repository.language')
                ->title('Primary Language')
                ->options([
                    'php' => 'PHP',
                    'python' => 'Python',
                    'javascript' => 'JavaScript',
                    'typescript' => 'TypeScript',
                    'java' => 'Java',
                    'csharp' => 'C#',
                    'go' => 'Go',
                    'ruby' => 'Ruby',
                ])
                ->empty('Select primary language')
                ->help('The primary programming language used in this repository'),

            TextArea::make('repository.description')
                ->title('Description')
                ->rows(3)
                ->placeholder('Repository description')
                ->help('A brief description of the repository'),

            CheckBox::make('repository.active')
                ->title('Status')
                ->placeholder('Active')
                ->value(true)
                ->help('Whether agents should work with this repository'),
        ];
    }
}
