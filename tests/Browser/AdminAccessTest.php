<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Orchid\Platform\Models\Role;
use Tests\DuskTestCase;

class AdminAccessTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Setup admin user for tests
     */
    protected function setupAdmin()
    {
        // Create admin role
        $adminRole = Role::create([
            'slug' => 'admin',
            'name' => 'Administrator',
            'permissions' => [
                'platform.index' => true,
                'platform.systems' => true,
                'platform.systems.roles' => true,
                'platform.systems.users' => true,
                'platform.agents.dashboard' => true,
                'platform.agents.repository' => true,
                'platform.agents.task' => true,
                'platform.agents.monitoring' => true,
            ],
        ]);

        // Create admin user
        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Assign admin role to user
        $user->addRole($adminRole);

        return $user;
    }

    /**
     * Test admin login page loads correctly
     */
    public function test_admin_login_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/login')
                ->assertSee('Login')
                ->assertPresent('form')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->screenshot('admin-login-page');
        });
    }

    /**
     * Test admin login with valid credentials
     */
    public function test_admin_login_with_valid_credentials()
    {
        $user = $this->setupAdmin();

        $this->browse(function (Browser $browser) {
            // First clear any cookies to ensure clean state
            $browser->visit('/')->driver->manage()->deleteAllCookies();

            $browser->visit('/admin/login')
                ->type('email', 'admin@example.com')
                ->type('password', 'password')
                ->press('Login')
                ->pause(5000) // Give more time for redirect and page load
                ->assertSee('Dashboard', false) // Just check Dashboard text appears somewhere
                ->screenshot('admin-logged-in');
        });
    }

    /**
     * Test repository page access after login
     */
    public function test_repository_page_access()
    {
        $user = $this->setupAdmin();

        $this->browse(function (Browser $browser) {
            // First clear any cookies to ensure clean state
            $browser->visit('/')->driver->manage()->deleteAllCookies();

            $browser->visit('/admin/login')
                ->type('email', 'admin@example.com')
                ->type('password', 'password')
                ->press('Login')
                ->pause(5000) // More time to load
                ->assertSee('Dashboard', false)

                // Try to find and click repositories, be flexible with text matching
                ->whenAvailable('a:contains("Repositories")', function ($link) {
                    $link->click();
                })
                ->pause(3000) // Wait for page to load
                ->assertSee('Repositories', false) // Case-insensitive check
                ->screenshot('repository-page');
        });
    }
}
