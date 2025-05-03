<?php

declare(strict_types=1);

use App\Orchid\Screens\Agent\AgentEditScreen;
use App\Orchid\Screens\Agent\AgentListScreen;
use App\Orchid\Screens\DashboardScreen;
use App\Orchid\Screens\Examples\ExampleActionsScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleGridScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\Monitoring\AlertSettingsScreen;
use App\Orchid\Screens\Monitoring\ErrorDashboardScreen;
use App\Orchid\Screens\Monitoring\ErrorDetailScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Repository\RepositoryEditScreen;
use App\Orchid\Screens\Repository\RepositoryListScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\Task\TaskEditScreen;
use App\Orchid\Screens\Task\TaskListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Custom Dashboard
Route::screen('/dashboard', DashboardScreen::class)
    ->name('platform.dashboard')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->push('Dashboard', route('platform.dashboard')));

// Agent Management
Route::screen('agents', AgentListScreen::class)
    ->name('platform.agent.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.dashboard')
        ->push('Agents', route('platform.agent.list')));

Route::screen('agents/{agent?}', AgentEditScreen::class)
    ->name('platform.agent.edit')
    ->breadcrumbs(fn (Trail $trail, $agent = null) => $trail
        ->parent('platform.agent.list')
        ->push($agent ? 'Edit Agent' : 'Create Agent', route('platform.agent.edit', $agent)));

// Task Management
Route::screen('tasks', TaskListScreen::class)
    ->name('platform.task.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.dashboard')
        ->push('Tasks', route('platform.task.list')));

Route::screen('tasks/{task?}', TaskEditScreen::class)
    ->name('platform.task.edit')
    ->breadcrumbs(fn (Trail $trail, $task = null) => $trail
        ->parent('platform.task.list')
        ->push($task ? 'Edit Task' : 'Create Task', route('platform.task.edit', $task)));

// Repository Management
Route::screen('repositories', RepositoryListScreen::class)
    ->name('platform.repository.list')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.dashboard')
        ->push('Repositories', route('platform.repository.list')));

Route::screen('repositories/{repository?}', RepositoryEditScreen::class)
    ->name('platform.repository.edit')
    ->breadcrumbs(fn (Trail $trail, $repository = null) => $trail
        ->parent('platform.repository.list')
        ->push($repository ? 'Edit Repository' : 'Create Repository', route('platform.repository.edit', $repository)));

// Monitoring & Analytics
Route::screen('monitoring/errors', ErrorDashboardScreen::class)
    ->name('platform.monitoring.errors')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.dashboard')
        ->push('Error Monitoring', route('platform.monitoring.errors')));

Route::screen('monitoring/errors/{id}', ErrorDetailScreen::class)
    ->name('platform.monitoring.error.view')
    ->breadcrumbs(fn (Trail $trail, $id) => $trail
        ->parent('platform.monitoring.errors')
        ->push('Error Details', route('platform.monitoring.error.view', $id)));

Route::screen('monitoring/alerts', AlertSettingsScreen::class)
    ->name('platform.monitoring.alerts')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.monitoring.errors')
        ->push('Alert Settings', route('platform.monitoring.alerts')));

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// Example...
Route::screen('example', ExampleScreen::class)
    ->name('platform.example')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Example Screen'));

Route::screen('/examples/form/fields', ExampleFieldsScreen::class)->name('platform.example.fields');
Route::screen('/examples/form/advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');
Route::screen('/examples/form/editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
Route::screen('/examples/form/actions', ExampleActionsScreen::class)->name('platform.example.actions');

Route::screen('/examples/layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
Route::screen('/examples/grid', ExampleGridScreen::class)->name('platform.example.grid');
Route::screen('/examples/charts', ExampleChartsScreen::class)->name('platform.example.charts');
Route::screen('/examples/cards', ExampleCardsScreen::class)->name('platform.example.cards');
