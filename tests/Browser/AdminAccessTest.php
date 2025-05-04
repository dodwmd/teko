<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Orchid\Platform\Models\Role;

class AdminAccessTest extends BaseBrowserTest
{
    use DatabaseMigrations;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set necessary configuration for browser console logs
        Browser::$storeConsoleLogAt = base_path('tests/Browser/console');
    }

    /**
     * Setup admin user for tests
     */
    protected function setupAdmin()
    {
        // Create admin role
        $adminRole = Role::where('slug', 'admin')->first();
        if (! $adminRole) {
            $adminRole = Role::create([
                'slug' => 'admin',
                'name' => 'Administrator',
                'permissions' => [
                    'platform.index' => true,
                    'platform.systems' => true,
                    'platform.systems.roles' => true,
                    'platform.systems.users' => true,
                    'platform.main' => true,
                    'platform.dashboard' => true,
                    'platform.agents.dashboard' => true,
                    'platform.agents.repository' => true,
                    'platform.agents.task' => true,
                    'platform.agents.monitoring' => true,
                    'platform.agent.list' => true,
                    'platform.agent.edit' => true,
                    'platform.task.list' => true,
                    'platform.task.edit' => true,
                    'platform.repository.list' => true,
                    'platform.repository.edit' => true,
                    'platform.monitoring.errors' => true,
                    'platform.monitoring.alerts' => true,
                ],
            ]);
        }

        // Create or update admin user
        $user = User::where('email', 'admin@example.com')->first();
        if (! $user) {
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);

            // Assign admin role to user
            $user->addRole($adminRole);
        }

        return $user;
    }

    /**
     * Test admin login page loads correctly
     */
    public function test_admin_login_page_loads(): void
    {
        $this->setupAdmin();

        $this->browse(function (Browser $browser) {
            // Disable console logging for this test to avoid permission issues
            $browser->disableConsoleLog()
                ->visit('/admin/login')
                ->waitForText('Login')
                ->assertSee('Login')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]');
        });
    }

    /**
     * Test admin login with valid credentials using direct Laravel authentication
     */
    public function test_admin_login_with_valid_credentials(): void
    {
        // Create/ensure admin user exists
        $user = $this->setupAdmin();

        $this->browse(function (Browser $browser) use ($user) {
            // Disable console logging for this test
            $browser->disableConsoleLog()
                // Use Laravel's direct login capability
                ->loginAs($user->id)
                ->visit('/admin/dashboard')
                ->pause(2000)
                ->assertPathIsNot('/admin/login')
                ->assertSee('Dashboard', false); // Case insensitive search
        });
    }

    /**
     * Test repository page access after login
     */
    public function test_repository_page_access(): void
    {
        // Create/ensure admin user exists
        $user = $this->setupAdmin();

        $this->browse(function (Browser $browser) use ($user) {
            // Disable console logging for this test
            $browser->disableConsoleLog()
                // Use Laravel's direct login capability
                ->loginAs($user->id)
                ->visit('/admin/dashboard')
                ->pause(2000)
                ->assertPathIsNot('/admin/login')
                ->visit('/admin/repositories')
                ->assertSee('Repositories', false);
        });
    }
}
