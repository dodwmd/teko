<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Agent;

use App\Models\Agent;
use App\Orchid\Layouts\Agent\AgentListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class AgentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'agents' => Agent::filters()
                ->defaultSort('name')
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Agent Management';
    }

    /**
     * The description displayed in the header.
     */
    public function description(): ?string
    {
        return 'Manage AI agents and their configuration';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Create Agent')
                ->icon('plus')
                ->route('platform.agent.edit'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]
     *
     * @psalm-return list{AgentListLayout::class}
     */
    public function layout(): array
    {
        return [
            AgentListLayout::class,
        ];
    }
}
