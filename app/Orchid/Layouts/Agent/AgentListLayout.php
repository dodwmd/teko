<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Agent;

use App\Models\Agent;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class AgentListLayout extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'agents';

    /**
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('checkbox', '')
                ->align(TD::ALIGN_CENTER)
                ->width('1%')
                ->render(function (Agent $agent) {
                    return '<input type="checkbox" 
                                class="form-check-input" 
                                name="id[]" 
                                value="'.$agent->id.'" 
                                form="agent-actions-form">';
                }),

            TD::make('name', 'Name')
                ->sort()
                ->filter()
                ->render(function (Agent $agent) {
                    return Link::make($agent->name)
                        ->route('platform.agent.edit', $agent->id);
                }),

            TD::make('type', 'Type')
                ->sort()
                ->filter()
                ->render(function (Agent $agent) {
                    return ucfirst($agent->type);
                }),

            TD::make('language', 'Language')
                ->sort()
                ->filter()
                ->render(function (Agent $agent) {
                    return $agent->language ? ucfirst($agent->language) : 'Any';
                }),

            TD::make('status', 'Status')
                ->sort()
                ->render(function (Agent $agent) {
                    return $agent->enabled
                        ? '<span class="badge bg-success">Enabled</span>'
                        : '<span class="badge bg-secondary">Disabled</span>';
                }),

            TD::make('last_active_at', 'Last Active')
                ->sort()
                ->render(function (Agent $agent) {
                    return $agent->last_active_at
                        ? $agent->last_active_at->diffForHumans()
                        : 'Never';
                }),

            TD::make('description', 'Description')
                ->render(function (Agent $agent) {
                    return Str::limit($agent->description, 100);
                }),

            TD::make('actions', 'Actions')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function (Agent $agent) {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            Link::make('Edit')
                                ->icon('pencil')
                                ->route('platform.agent.edit', $agent->id),

                            Button::make($agent->enabled ? 'Disable' : 'Enable')
                                ->icon($agent->enabled ? 'power' : 'check')
                                ->method('toggleAgent', ['id' => $agent->id])
                                ->confirm($agent->enabled
                                    ? 'Are you sure you want to disable this agent?'
                                    : 'Are you sure you want to enable this agent?'),

                            Button::make('Delete')
                                ->icon('trash')
                                ->method('removeAgent', ['id' => $agent->id])
                                ->confirm('Are you sure you want to delete this agent?'),
                        ]);
                }),
        ];
    }
}
