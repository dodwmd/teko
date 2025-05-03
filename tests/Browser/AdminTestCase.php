<?php

namespace Tests\Browser;

use App\Models\User;
use Orchid\Platform\Models\Role;
use Tests\DuskTestCase;

abstract class AdminTestCase extends DuskTestCase
{
    /**
     * Create the admin user for testing.
     */
    protected function createAdminUser(): User
    {
        // Create the admin role if it doesn't exist
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
                    'platform.agents.dashboard' => true,
                    'platform.agents.repository' => true,
                    'platform.agents.task' => true,
                    'platform.agents.monitoring' => true,
                ],
            ]);
        }

        // Create or retrieve the admin user
        $user = User::where('email', 'admin@example.com')->first();
        if (! $user) {
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);

            // Assign the admin role to the user
            $user->addRole($adminRole);
        }

        // Force update the Browser user resolver to use this specific user
        \Laravel\Dusk\Browser::$userResolver = function () use ($user) {
            return $user;
        };

        return $user;
    }

    /**
     * Set up the browser for each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Always start with a fresh database
        $this->artisan('migrate:fresh');

        // Create the admin user by default for all tests
        $this->createAdminUser();
    }
}
