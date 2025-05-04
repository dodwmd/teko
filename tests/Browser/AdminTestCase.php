<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchid\Platform\Models\Role;

abstract class AdminTestCase extends BaseBrowserTest
{
    /**
     * Flag to track if admin setup has been done
     */
    protected static $adminSetupComplete = false;

    /**
     * Create admin user for testing
     */
    protected function createAdminUser()
    {
        // Create admin role if it doesn't exist
        $role = Role::where('slug', 'admin')->first();
        if (! $role) {
            $role = Role::create([
                'name' => 'Admin',
                'slug' => 'admin',
                'permissions' => [
                    'platform.index' => 1,
                    'platform.systems' => 1,
                    'platform.systems.roles' => 1,
                    'platform.systems.users' => 1,
                    'platform.systems.attachment' => 1,
                    'platform.main' => 1,
                    'platform.dashboard' => 1,
                ],
            ]);
        }

        // Create the admin user if it doesn't exist
        $admin = User::where('email', 'admin@example.com')->first();
        if (! $admin) {
            $admin = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'permissions' => [
                    'platform.index' => 1,
                    'platform.systems' => 1,
                    'platform.systems.roles' => 1,
                    'platform.systems.users' => 1,
                    'platform.systems.attachment' => 1,
                    'platform.main' => 1,
                    'platform.dashboard' => 1,
                ],
            ]);

            // Assign admin role to the user
            $admin->roles()->sync([$role->id]);
        }

        return $admin;
    }

    /**
     * Login to the admin panel
     */
    protected function loginAsAdmin(Browser $browser)
    {
        // Make sure we have a fresh instance
        $this->createAdminUser();

        $browser->visit('/admin/login')
            ->assertSee('Login')
            ->type('email', 'admin@example.com')
            ->type('password', 'password')
            ->press('Login')
            ->waitForLocation('/admin')
            ->assertPathIsNot('/admin/login');

        return $browser;
    }

    /**
     * Set up the test environment more efficiently
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$adminSetupComplete) {
            $this->setupAdminTestEnvironment();
            static::$adminSetupComplete = true;
        } else {
            // For subsequent tests, just clear relevant tables instead of full migration
            $this->truncateTestTables();
        }

        // Create the admin user
        $this->createAdminUser();
    }

    /**
     * Initial setup for admin tests
     */
    protected function setupAdminTestEnvironment(): void
    {
        // Only run migrations if tables don't exist
        if (! Schema::hasTable('users') || ! Schema::hasTable('roles')) {
            // Need to ensure platform migrations are run
            \Log::info('Running migrations for Dusk test admin environment');
            $this->artisan('migrate');

            // Specifically check for the Orchid/Platform tables
            if (! Schema::hasTable('roles')) {
                \Log::warning('Roles table still missing after migration, attempting to run platform migrations');
                $this->artisan('orchid:admin', ['name' => 'admin', 'email' => 'admin@example.com', 'password' => 'password']);
            }
        } else {
            $this->truncateTestTables();
        }
    }

    /**
     * Efficiently clear test tables without full migration
     */
    protected function truncateTestTables(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // List of tables that should be truncated for tests
        $tables = [
            'comments',
            'tasks',
            'repositories',
            'attachments',
            'attachmentable',
            'telescope_entries',
            'telescope_entries_tags',
            'sessions',
            'role_users',
        ];

        // Only truncate tables that exist
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                try {
                    DB::table($table)->truncate();
                } catch (\Exception $e) {
                    // Log error but continue
                    \Log::warning("Could not truncate table {$table}: {$e->getMessage()}");
                }
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
