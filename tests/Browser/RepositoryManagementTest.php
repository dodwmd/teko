<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Orchid\Platform\Models\Role;
use Tests\DuskTestCase;

class RepositoryManagementTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Administrator',
            'permissions' => ['platform.index' => true, 'platform.systems.roles' => true, 'platform.systems.users' => true, /* Add other necessary permissions */ ],
        ]);

        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'repo-test@teko.com',
        ]);
        $this->adminUser->addRole($adminRole);
    }

    /**
     * Test that the repository list screen is accessible.
     */
    public function test_repository_list_screen_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                    ->visit('/admin/repositories') // Adjust route if necessary
                    ->assertSee('Repositories') // Check for the screen title
                    ->assertPathIs('/admin/repositories'); // Verify the path
        });
    }

    // TODO: Add tests for creating, viewing, and editing repositories
}
