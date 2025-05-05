<?php

namespace App\Orchid\Screens\Examples;

use App\Orchid\Layouts\Examples\TabMenuExample;
use Orchid\Screen\Action;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class ExampleLayoutsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Layout Overview';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'A comprehensive guide to the different layout options available.';
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return (\Orchid\Screen\Layouts\Accordion|\Orchid\Screen\Layouts\Block|\Orchid\Screen\Layouts\Columns|\Orchid\Screen\Layouts\Tabs|\Orchid\Screen\Layouts\View|string)[]
     *
     * @throws \Throwable
     *
     * @psalm-return list{\Orchid\Screen\Layouts\Block, \Orchid\Screen\Layouts\Tabs, TabMenuExample::class, \Orchid\Screen\Layouts\View, \Orchid\Screen\Layouts\Columns, \Orchid\Screen\Layouts\Accordion}
     */
    public function layout(): array
    {
        return [

            Layout::block(Layout::view('platform::dummy.block'))
                ->title('Block Header')
                ->description('Excellent description that editing or views in block'),

            Layout::tabs([
                'Example Tab 1' => Layout::view('platform::dummy.block'),
                'Example Tab 2' => Layout::view('platform::dummy.block'),
                'Example Tab 3' => Layout::view('platform::dummy.block'),
            ]),

            TabMenuExample::class,
            Layout::view('platform::dummy.block'),

            Layout::columns([
                Layout::view('platform::dummy.block'),
                Layout::view('platform::dummy.block'),
                Layout::view('platform::dummy.block'),
            ]),

            Layout::accordion([
                'Collapsible Group Item #1' => Layout::view('platform::dummy.block'),
                'Collapsible Group Item #2' => Layout::view('platform::dummy.block'),
                'Collapsible Group Item #3' => Layout::view('platform::dummy.block'),
            ]),

        ];
    }
}
