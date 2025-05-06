<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Orchid\Platform\Models\Role;
use Tests\DuskTestCase;

class TaskManagementTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Administrator',
            'permissions' => ['platform.index' => true, 'platform.systems.roles' => true, 'platform.systems.users' => true/* Add other necessary permissions */],
        ]);

        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'task-test@teko.com',
        ]);
        $this->adminUser->addRole($adminRole);
    }

    /**
     * Test that the task list screen is accessible.
     */
    public function test_task_list_screen_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/admin/tasks') // Adjust route if necessary
                ->assertSee('Tasks') // Check for the screen title
                ->assertPathIs('/admin/tasks'); // Verify the path
        });
    }

    // TODO: Add tests for creating, viewing, and editing tasks
}
