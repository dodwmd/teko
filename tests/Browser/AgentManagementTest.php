<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Config;
use Laravel\Dusk\Browser;
use Orchid\Platform\Models\Role;
use Tests\DuskTestCase;

class AgentManagementTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Administrator',
            'permissions' => [
                'platform.index' => true,
                'platform.systems.roles' => true,
                'platform.systems.users' => true,
                'platform.systems.agents' => true, // Add agent permission
            ],
        ]);

        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'agent-test@teko.com',
        ]);
        $this->adminUser->addRole($adminRole);
    }

    /**
     * Test if an admin user can view the agent list page.
     *
     * @throws \Throwable
     */
    public function test_admin_can_view_agent_list(): void
    {
        // Ensure the 'web' guard is the default for Dusk
        // Config::set('auth.defaults.guard', 'web'); // Removed, potentially unnecessary/interfering

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser, 'web')
                ->visit('/admin/agents')
                ->assertPathIs('/admin/agents')
                ->assertSee('Agents'); // Check for a title or header
        });
    }

    /**
     * A Dusk test example.
     */
    public function test_example(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('Laravel');
        });
    }
}
