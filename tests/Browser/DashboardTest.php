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

    /**
     * Test that the main dashboard screen is accessible to an admin.
     */
    public function test_dashboard_screen_can_be_rendered(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Administrator',
            'permissions' => ['platform.index' => true, 'platform.systems.roles' => true, /* add other essential perms */],
        ]);

        // Create admin user and assign the role
        $adminUser = User::factory()->create([
            'email' => 'dashboard-test@teko.com',
        ]);
        $adminUser->addRole($adminRole);

        $this->browse(function (Browser $browser) use ($adminUser) {
            $browser->loginAs($adminUser)
                    ->visit('/admin/main') // Adjust route if necessary
                    ->assertSee('Dashboard') // Check for a common element
                    ->assertPathIs('/admin/main'); // Verify the path
        });
    }
}
