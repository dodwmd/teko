<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Dashboard')
                ->icon('bs.speedometer2')
                ->title('Teko System')
                ->route('platform.dashboard'),

            Menu::make('Agents')
                ->icon('bs.robot')
                ->route('platform.agent.list')
                ->permission('platform.agent.list'),

            Menu::make('Tasks')
                ->icon('bs.clipboard-check')
                ->route('platform.task.list')
                ->badge(fn () => 5, Color::INFO),

            Menu::make('Repositories')
                ->icon('bs.git')
                ->route('platform.repository.list')
                ->divider(),

            Menu::make('Error Monitoring')
                ->icon('bs.exclamation-triangle')
                ->route('platform.monitoring.errors')
                ->badge(fn () => 3, Color::DANGER),

            Menu::make('Alert Settings')
                ->icon('bs.bell')
                ->route('platform.monitoring.alerts')
                ->permission('platform.systems.settings'),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make('Documentation')
                ->title('Resources')
                ->icon('bs.book')
                ->url('https://orchid.software/en/docs')
                ->target('_blank'),

            Menu::make('GitHub Repository')
                ->icon('bs.github')
                ->url('https://github.com/dodwmd/teko')
                ->target('_blank'),

            Menu::make('Version')
                ->icon('bs.code')
                ->badge(fn () => '1.0.0-dev', Color::DARK),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),

            ItemPermission::group(__('Agent Management'))
                ->addPermission('platform.agent.list', __('View Agents'))
                ->addPermission('platform.agent.edit', __('Edit Agents')),

            ItemPermission::group(__('Task Management'))
                ->addPermission('platform.task.list', __('View Tasks'))
                ->addPermission('platform.task.edit', __('Edit Tasks')),

            ItemPermission::group(__('Repository Management'))
                ->addPermission('platform.repository.list', __('View Repositories'))
                ->addPermission('platform.repository.edit', __('Edit Repositories')),

            ItemPermission::group(__('Monitoring'))
                ->addPermission('platform.monitoring.errors', __('View Error Logs'))
                ->addPermission('platform.monitoring.alerts', __('Configure Alerts')),
        ];
    }
}
