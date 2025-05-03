<?php

namespace Tests;

use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        // Kill any existing chromedriver processes
        if (! static::runningInSail()) {
            static::$chromeProcess = static::startChromeDriver();
        }

        // Set up browser authentication resolver
        Browser::$userResolver = function () {
            return User::where('email', 'admin@example.com')->first();
        };

        // Note: We can't share view errors here because the view factory isn't ready yet
    }

    /**
     * Setup the database for Dusk tests.
     * This only runs once before all tests in the class.
     */
    #[BeforeClass]
    public static function setupDatabase(): void
    {
        // Check if we need to run migrations
        static::$migrationsRun = static::$migrationsRun ?? false;

        if (! static::$migrationsRun) {
            // Set flag to prevent multiple migrations
            static::$migrationsRun = true;

            // Run migrations for the Dusk database connection
            $app = require __DIR__.'/../bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

            // Use the artisan command to refresh the database
            Artisan::call('migrate:fresh', ['--env' => 'dusk.local']);

            \Log::info('Migrations have been run for Dusk tests');
        }
    }

    /**
     * Flag to track if migrations have run.
     */
    protected static $migrationsRun = false;

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--ignore-certificate-errors',  // Add this to ignore certificate errors
            '--disable-extensions',         // Add this to disable extensions
            '--disable-gpu',                // Add this to disable GPU
            '--no-sandbox',                 // Add this for CI environments
            '--disable-dev-shm-usage',      // Add this for CI environments
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Get the URL for the Dusk tests.
     */
    protected function baseUrl()
    {
        return 'http://localhost:8000';
    }
}
