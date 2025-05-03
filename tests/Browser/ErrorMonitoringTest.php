<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\AdminLogin;

class ErrorMonitoringTest extends AdminTestCase
{
    use DatabaseMigrations;

    /**
     * Test the error log list screen loads
     */
    public function test_error_log_list_screen(): void
    {
        // Create test data
        $this->createAdminUser();

        // Insert some dummy logs directly to the logs table (since we can't easily generate real errors)
        DB::table('telescope_entries')->insert([
            'uuid' => uuid_create(),
            'batch_id' => uuid_create(),
            'family_hash' => null,
            'type' => 'exception',
            'content' => json_encode([
                'class' => 'RuntimeException',
                'message' => 'Test error for browser testing',
                'trace' => [],
                'line' => 123,
                'file' => 'TestFile.php',
            ]),
            'created_at' => now(),
            'sequence' => 1,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Error Logs')
                ->waitForLocation('/admin/monitoring/errors')
                ->assertSee('Error Logs')
                ->assertPresent('.table')
                ->screenshot('error-logs');
        });
    }

    /**
     * Test error log filtering works
     */
    public function test_error_log_filtering(): void
    {
        // Create test data
        $this->createAdminUser();

        // Insert some dummy logs with different types
        DB::table('telescope_entries')->insert([
            [
                'uuid' => uuid_create(),
                'batch_id' => uuid_create(),
                'family_hash' => null,
                'type' => 'exception',
                'content' => json_encode([
                    'class' => 'RuntimeException',
                    'message' => 'Exception error for testing',
                    'trace' => [],
                    'line' => 123,
                    'file' => 'TestFile.php',
                ]),
                'created_at' => now(),
                'sequence' => 1,
            ],
            [
                'uuid' => uuid_create(),
                'batch_id' => uuid_create(),
                'family_hash' => null,
                'type' => 'log',
                'content' => json_encode([
                    'level' => 'error',
                    'message' => 'Error log for testing',
                    'context' => [],
                ]),
                'created_at' => now(),
                'sequence' => 2,
            ],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Error Logs')
                ->waitForLocation('/admin/monitoring/errors')
                    // Test the filter
                ->select('type', 'exception')
                ->press('Filter')
                ->waitForText('Exception error for testing')
                ->assertSee('Exception error for testing')
                ->assertDontSee('Error log for testing')
                ->screenshot('error-logs-filter');
        });
    }

    /**
     * Test error detail view
     */
    public function test_error_detail_view(): void
    {
        // Create test data
        $this->createAdminUser();

        // Insert a dummy log
        $uuid = uuid_create();
        DB::table('telescope_entries')->insert([
            'uuid' => $uuid,
            'batch_id' => uuid_create(),
            'family_hash' => null,
            'type' => 'exception',
            'content' => json_encode([
                'class' => 'RuntimeException',
                'message' => 'Detailed error for testing',
                'trace' => [
                    [
                        'file' => 'TestFile.php',
                        'line' => 123,
                        'function' => 'testFunction',
                    ],
                ],
                'line' => 123,
                'file' => 'TestFile.php',
            ]),
            'created_at' => now(),
            'sequence' => 1,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Error Logs')
                ->waitForLocation('/admin/monitoring/errors')
                    // Click on error to view detail
                ->clickLink('Detailed error for testing')
                ->waitForLocation('/admin/monitoring/errors/*')
                ->assertSee('RuntimeException')
                ->assertSee('Detailed error for testing')
                ->assertSee('Stack Trace')
                ->assertSee('TestFile.php')
                ->assertSee('line 123')
                ->screenshot('error-detail');
        });
    }

    /**
     * Test the alert settings screen
     */
    public function test_alert_settings_screen(): void
    {
        $this->createAdminUser();

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Alert Settings')
                ->waitForLocation('/admin/monitoring/alerts/settings')
                ->assertSee('Alert Settings')
                ->assertPresent('form')
                ->assertPresent('input[name="settings[email_notifications]"]')
                ->assertPresent('input[name="settings[email_recipients]"]')
                ->assertPresent('input[name="settings[slack_notifications]"]')
                ->assertPresent('input[name="settings[slack_webhook]"]')
                ->assertPresent('input[name="settings[telegram_notifications]"]')
                ->screenshot('alert-settings');
        });
    }

    /**
     * Test saving alert settings
     */
    public function test_saving_alert_settings(): void
    {
        $this->createAdminUser();

        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminLogin)
                ->login()
                ->clickLink('Alert Settings')
                ->waitForLocation('/admin/monitoring/alerts/settings')
                    // Toggle email notifications on
                ->click('input[name="settings[email_notifications]"]')
                ->type('settings[email_recipients]', 'test@example.com')
                    // Save settings
                ->press('Save')
                ->waitForText('Alert settings have been updated')
                ->screenshot('alert-settings-saved');

            // Verify settings were saved by checking the form values after reload
            $browser->refresh()
                ->waitForLocation('/admin/monitoring/alerts/settings')
                ->assertChecked('settings[email_notifications]')
                ->assertInputValue('settings[email_recipients]', 'test@example.com')
                ->screenshot('alert-settings-verified');
        });
    }
}
