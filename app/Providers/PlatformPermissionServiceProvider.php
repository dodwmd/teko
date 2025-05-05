<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;

class PlatformPermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Dashboard $dashboard): void
    {
        $permissions = ItemPermission::group(__('System'))
            ->addPermission('platform.index', __('Main'))
            ->addPermission('platform.main', __('Main')) // Explicitly register platform.main
            ->addPermission('platform.systems', __('Systems'))
            ->addPermission('platform.systems.index', __('Settings'))
            ->addPermission('platform.systems.roles', __('Roles'))
            ->addPermission('platform.systems.users', __('Users'))
            ->addPermission('platform.systems.attachment', __('Attachment'));
        // Add other core permissions used in the seeder if needed

        $dashboard->registerPermissions($permissions);
    }
}
