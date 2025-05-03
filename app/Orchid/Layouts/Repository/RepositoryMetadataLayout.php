<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Repository;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Code;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Rows;

class RepositoryMetadataLayout extends Rows
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
            Matrix::make('repository.languages')
                ->title('Languages')
                ->columns([
                    'language' => 'Language',
                    'percentage' => 'Percentage',
                ])
                ->fields([
                    'language' => Select::make()
                        ->options([
                            'php' => 'PHP',
                            'python' => 'Python',
                            'javascript' => 'JavaScript',
                            'typescript' => 'TypeScript',
                            'java' => 'Java',
                            'csharp' => 'C#',
                            'go' => 'Go',
                            'ruby' => 'Ruby',
                            'html' => 'HTML',
                            'css' => 'CSS',
                            'shell' => 'Shell',
                            'sql' => 'SQL',
                            'other' => 'Other',
                        ]),
                    'percentage' => Input::make()->type('number')->min(1)->max(100),
                ])
                ->value(function ($repository) {
                    if (! $repository->languages) {
                        return [];
                    }

                    // Convert languages array to matrix format
                    $result = [];
                    foreach ($repository->languages as $language => $percentage) {
                        $result[] = [
                            'language' => $language,
                            'percentage' => $percentage,
                        ];
                    }

                    return $result;
                })
                ->help('Distribution of programming languages in the repository'),

            Code::make('repository.metadata')
                ->title('Additional Metadata')
                ->language('json')
                ->lineNumbers()
                ->value(function ($repository) {
                    return json_encode($repository->metadata ?? [], JSON_PRETTY_PRINT);
                })
                ->help('Custom metadata for this repository in JSON format'),
        ];
    }
}
