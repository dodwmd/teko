<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Agent;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class DashboardScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $activeAgents = Agent::where('enabled', true)->count();
        $totalAgents = Agent::count();

        $agentTypes = Agent::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        $recentlyActiveAgents = Agent::where('last_active_at', '>=', now()->subHours(24))
            ->orderBy('last_active_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'metrics' => [
                'active_agents' => $activeAgents,
                'total_agents' => $totalAgents,
                'agent_types' => $agentTypes,
            ],
            'recently_active' => $recentlyActiveAgents,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Teko Dashboard';
    }

    /**
     * The description displayed in the header.
     */
    public function description(): ?string
    {
        return 'Monitor your AI agent system';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Manage Agents')
                ->icon('list')
                ->route('platform.agent.list'),

            Link::make('Create Agent')
                ->icon('plus')
                ->route('platform.agent.edit'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Active Agents' => 'metrics.active_agents',
                'Total Agents' => 'metrics.total_agents',
            ]),

            Layout::columns([
                Layout::view('platform.agent.dashboard.agent-types'),
                Layout::view('platform.agent.dashboard.system-health'),
            ]),

            Layout::view('platform.agent.dashboard.recently-active'),
        ];
    }
}
