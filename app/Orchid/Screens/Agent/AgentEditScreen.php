<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Agent;

use App\Models\Agent;
use App\Orchid\Layouts\Agent\AgentEditLayout;
use App\Orchid\Layouts\Agent\AgentMetadataLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class AgentEditScreen extends Screen
{
    /**
     * @var Agent
     */
    public $agent;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Agent $agent): iterable
    {
        return [
            'agent' => $agent,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->agent->exists ? 'Edit Agent: '.$this->agent->name : 'Create Agent';
    }

    /**
     * The description displayed in the header.
     */
    public function description(): ?string
    {
        return 'Configure agent properties and capabilities';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->icon('save')
                ->method('save'),

            Button::make('Remove')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->agent->exists),
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
            Layout::tabs([
                'General' => [
                    AgentEditLayout::class,
                ],
                'Metadata' => [
                    AgentMetadataLayout::class,
                ],
            ]),
        ];
    }

    /**
     * Save the agent.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Agent $agent, Request $request)
    {
        $data = $request->validate([
            'agent.name' => 'required|string|max:255',
            'agent.type' => 'required|string|max:255',
            'agent.language' => 'nullable|string|max:255',
            'agent.enabled' => 'boolean',
            'agent.description' => 'nullable|string',
            'agent.capabilities' => 'nullable|array',
            'agent.metadata' => 'nullable|array',
            'agent.configuration' => 'nullable|array',
        ]);

        $agent->fill($data['agent'])->save();

        Toast::info($agent->wasRecentlyCreated ? 'Agent created successfully' : 'Agent updated successfully');

        return redirect()->route('platform.agent.list');
    }

    /**
     * Remove the agent.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Agent $agent)
    {
        $agent->delete();

        Toast::info('Agent deleted successfully');

        return redirect()->route('platform.agent.list');
    }
}
