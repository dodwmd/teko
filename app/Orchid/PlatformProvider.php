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

            Menu::make('Users')
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title('Access Controls'),

            Menu::make('Roles')
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make('Documentation')
                ->title('Resources')
                ->icon('bs.book')
                ->url('https://github.com/dodwmd/teko/wiki')
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
     * Register the application's permissions.
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group('System')
                ->addPermission('platform.systems.roles', 'Roles')
                ->addPermission('platform.systems.users', 'Users'),

            ItemPermission::group('Agent Management')
                ->addPermission('platform.agent.list', 'View Agents')
                ->addPermission('platform.agent.edit', 'Edit Agents'),

            ItemPermission::group('Task Management')
                ->addPermission('platform.task.list', 'View Tasks')
                ->addPermission('platform.task.edit', 'Edit Tasks'),

            ItemPermission::group('Repository Management')
                ->addPermission('platform.repository.list', 'View Repositories')
                ->addPermission('platform.repository.edit', 'Edit Repositories'),

            ItemPermission::group('Monitoring')
                ->addPermission('platform.monitoring.errors', 'View Error Logs')
                ->addPermission('platform.monitoring.alerts', 'Configure Alerts'),
        ];
    }
}
