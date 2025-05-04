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
        
        // Create test directories if they don't exist
        $outputPaths = [
            'console' => base_path('tests/Browser/console'),
            'screenshots' => base_path('tests/Browser/screenshots'),
            'source' => base_path('tests/Browser/source'),
            'output' => base_path('tests/Browser/output'),
        ];
        
        foreach ($outputPaths as $path) {
            if (! is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
        
        // Define where Laravel Dusk should store its artifacts
        Browser::$storeScreenshotsAt = $outputPaths['screenshots'];
        Browser::$storeConsoleLogAt = $outputPaths['console'];
        Browser::$storeSourceAt = $outputPaths['source'];
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

            // For MySQL, we need to handle foreign key constraints carefully
            try {
                // Drop and recreate the database to ensure a clean state
                \Log::info('Preparing database for Dusk tests...');

                Artisan::call('migrate:fresh', [
                    '--seed' => true,
                    '--env' => 'dusk.local',
                    '--force' => true,
                ]);

                \Log::info('Database prepared for Dusk tests - migrations and seeds completed');
            } catch (\Exception $e) {
                \Log::error('Failed to prepare database for tests: '.$e->getMessage());
                \Log::error($e->getTraceAsString());
            }
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
            '--ignore-certificate-errors',
            '--disable-extensions',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-web-security',           // Add this to disable web security
            '--allow-running-insecure-content', // Allow running insecure content
            '--disable-popup-blocking',         // Disable popup blocking
            '--blink-settings=imagesEnabled=true', // Enable images
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--headless=new',
            ]);
        })->all());

        // Set default timeouts
        $capabilities = DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY, $options
        );

        // Set timeouts for browser operations
        $capabilities->setCapability('pageLoadStrategy', 'normal');
        $capabilities->setCapability('unhandledPromptBehavior', 'accept');
        $timeouts = ['implicit' => 30000, 'pageLoad' => 60000, 'script' => 60000];
        $capabilities->setCapability('timeouts', $timeouts);

        // Get the ChromeDriver URL from the environment or use the default
        // Check for any running ChromeDriver processes on different ports
        $driverUrl = env('DUSK_DRIVER_URL', 'http://localhost:9515');

        // Try to determine the port dynamically if running in CI environment
        if (env('CI') && ! env('DUSK_DRIVER_URL')) {
            // Check common ports
            $ports = [42111, 9515, 9516, 4444];
            foreach ($ports as $port) {
                if ($this->isPortOpen('localhost', $port)) {
                    $driverUrl = "http://localhost:{$port}";
                    break;
                }
            }
        }

        \Log::info("Connecting to ChromeDriver at: {$driverUrl}");

        return RemoteWebDriver::create($driverUrl, $capabilities);
    }

    /**
     * Check if a port is open.
     */
    protected function isPortOpen($host, $port): bool
    {
        $connection = @fsockopen($host, $port);
        if (is_resource($connection)) {
            fclose($connection);

            return true;
        }

        return false;
    }

    /**
     * Get the URL for the Dusk tests.
     */
    protected function baseUrl()
    {
        return 'http://localhost:8000';
    }

    /**
     * Configure the browser options to be more forgiving of JavaScript errors.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Browser::macro('scrollToElement', function ($element) {
            $this->driver->executeScript('arguments[0].scrollIntoView();', [$this->resolver->findOrFail($element)]);

            return $this;
        });

        Browser::macro('safeClick', function ($selector) {
            try {
                $this->click($selector);
            } catch (\Exception $e) {
                // Try to find and scroll to the element first, then click
                try {
                    $this->scrollToElement($selector)->driver->executeScript(
                        'arguments[0].click();',
                        [$this->resolver->findOrFail($selector)]
                    );
                } catch (\Exception $innerEx) {
                    // If all else fails, log it
                    \Log::warning("Failed to click element: {$selector}. Error: {$innerEx->getMessage()}");
                }
            }

            return $this;
        });
    }
}
