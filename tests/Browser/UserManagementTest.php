<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Orchid\Platform\Models\Role;
use Tests\DuskTestCase;

class UserManagementTest extends DuskTestCase
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
                'platform.systems.users' => true, // Essential for user management
            ],
        ]);

        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'user-test@teko.com',
        ]);
        $this->adminUser->addRole($adminRole);
    }

    /**
     * Test that the user list screen is accessible.
     */
    public function test_user_list_screen_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit(route('platform.systems.users')) // Use route helper
                ->assertSee('User Management') // Check for the correct screen title
                ->assertPathIs('/admin/users'); // Verify the path
        });
    }

    // TODO: Add tests for creating, viewing profile, and editing users
}
