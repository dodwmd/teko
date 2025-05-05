<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Orchid\Platform\Models\Role;
use Tests\DuskTestCase;

class RoleManagementTest extends DuskTestCase
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
                'platform.systems.roles' => true, // Essential for role management
                'platform.systems.users' => true,
            ],
        ]);

        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'role-test@teko.com',
        ]);
        $this->adminUser->addRole($adminRole);
    }

    /**
     * Test that the role list screen is accessible.
     */
    public function test_role_list_screen_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                    ->visit(route('platform.systems.roles')) // Use route helper
                    ->assertSee('Role Management') // Check for the screen title (Assuming this is the title)
                    ->assertPathIs('/admin/roles'); // Verify the path
        });
    }

    // TODO: Add tests for creating and editing roles
}
