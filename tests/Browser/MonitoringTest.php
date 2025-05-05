<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Orchid\Platform\Models\Role;
use Tests\DuskTestCase;

class MonitoringTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role with monitoring permissions
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Administrator',
            'permissions' => [
                'platform.index' => true,
                'platform.systems.roles' => true,
                'platform.systems.users' => true,
                'platform.monitoring' => true, // Grant access to monitoring section
            ],
        ]);

        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'monitor-test@teko.com',
        ]);
        $this->adminUser->addRole($adminRole);
    }

    /**
     * Test that the alert settings screen is accessible.
     */
    public function test_alert_settings_screen_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                    ->visit(route('platform.monitoring.alerts')) // Correct route name
                    ->assertSee('Alert Settings')
                    ->assertPathIs('/admin/monitoring/alerts'); // Correct path
        });
    }

    /**
     * Test that the error dashboard screen is accessible.
     */
    public function test_error_dashboard_screen_can_be_rendered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                    ->visit(route('platform.monitoring.errors')) // From ErrorDetailScreen commandBar
                    ->assertSee('Error Monitoring')
                    ->assertPathIs('/admin/monitoring/errors'); // Assuming path
        });
    }

    /**
     * Test that the error detail screen is accessible.
     */
    public function test_error_detail_screen_can_be_rendered(): void
    {
        // Create a dummy error log entry
        $logId = DB::table('logs')->insertGetId([
            'level' => 'error',
            'message' => 'Test error for detail screen',
            'context' => json_encode(['exception' => ['message' => 'Dummy exception', 'trace' => []]]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($logId) {
            $browser->loginAs($this->adminUser)
                    ->visit(route('platform.monitoring.error.view', ['id' => $logId]))
                    ->assertSee('Error Details')
                    ->assertPathIs("/admin/monitoring/errors/{$logId}");
        });
    }

    // TODO: Add test for Error Detail screen (requires creating a log entry)
}
