<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Orchid\Platform\Models\Role;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
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

        $this->user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->user->addRole($adminRole);
    }

    /**
     * Test that we can log in to the admin panel
     */
    public function test_can_login_to_admin_panel(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/login')
                ->screenshot('login-page');

            // Now try to login
            $browser->waitFor('input[name="email"]')
                ->type('input[name="email"]', 'admin@example.com')
                ->type('input[name="password"]', 'password')
                ->screenshot('before-submit')
                ->press('Login')
                ->pause(1000)
                ->screenshot('after-login');
        });
    }
}
