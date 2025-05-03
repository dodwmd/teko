<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Agent;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Rows;

class AgentEditLayout extends Rows
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
            Input::make('agent.name')
                ->title('Name')
                ->placeholder('Enter agent name')
                ->required(),

            Select::make('agent.type')
                ->title('Agent Type')
                ->options([
                    'codebase_analysis' => 'Codebase Analysis',
                    'code_implementation' => 'Code Implementation',
                    'code_review' => 'Code Review',
                    'story_management' => 'Story Management',
                    'scheduler' => 'Scheduler',
                    'language_specific' => 'Language Specific',
                ])
                ->required(),

            Select::make('agent.language')
                ->title('Programming Language')
                ->options([
                    '' => 'Any',
                    'php' => 'PHP',
                    'python' => 'Python',
                    'javascript' => 'JavaScript',
                    'typescript' => 'TypeScript',
                    'java' => 'Java',
                    'csharp' => 'C#',
                ])
                ->empty('Any')
                ->help('Only required for language-specific agents'),

            CheckBox::make('agent.enabled')
                ->title('Status')
                ->placeholder('Enabled')
                ->help('Enable or disable this agent'),

            TextArea::make('agent.description')
                ->title('Description')
                ->rows(3)
                ->placeholder('Describe the agent\'s purpose and functionality'),

            TextArea::make('agent.capabilities')
                ->title('Capabilities')
                ->rows(5)
                ->value(function ($agent) {
                    return is_array($agent->capabilities)
                        ? json_encode($agent->capabilities, JSON_PRETTY_PRINT)
                        : '[]';
                })
                ->help('JSON format list of agent capabilities'),
        ];
    }
}
