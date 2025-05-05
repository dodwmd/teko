<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Config;
use Laravel\Dusk\Browser;
use Orchid\Platform\Models\Role;
use Tests\DuskTestCase;

class AdminLoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * A Dusk test example.
     */
    public function test_admin_can_login(): void
    {
        // Create the admin role specifically for this test using Orchid's model
        $adminRole = Role::create(['slug' => 'admin', 'name' => 'Admin', 'permissions' => ['platform.index' => true]]); // Grant basic platform access

        // Create an admin user
        $adminUser = User::factory()->create();

        // Assign the admin role
        $adminUser->addRole($adminRole);

        // Ensure the 'web' guard is the default
        Config::set('auth.defaults.guard', 'web');

        $this->browse(function (Browser $browser) use ($adminUser) {
            // Use Dusk's loginAs method
            $browser->loginAs($adminUser, 'web');

            // Visit the target page directly after login
            $browser->visit('/admin/dashboard')
                ->assertPathIs('/admin/dashboard');
        });
    }
}
