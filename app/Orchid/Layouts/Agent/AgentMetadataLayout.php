<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Agent;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Code;
use Orchid\Screen\Layouts\Rows;

class AgentMetadataLayout extends Rows
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
            Code::make('agent.configuration')
                ->title('Configuration')
                ->language('json')
                ->lineNumbers()
                ->value(function ($agent) {
                    return json_encode($agent->configuration ?? [], JSON_PRETTY_PRINT);
                })
                ->help('Agent configuration settings in JSON format'),

            Code::make('agent.metadata')
                ->title('Metadata')
                ->language('json')
                ->lineNumbers()
                ->value(function ($agent) {
                    return json_encode($agent->metadata ?? [], JSON_PRETTY_PRINT);
                })
                ->help('Additional metadata for the agent in JSON format'),
        ];
    }
}
