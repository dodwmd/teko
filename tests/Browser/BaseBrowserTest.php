<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Base class for all browser tests to properly handle error handlers and cleanup
 */
class BaseBrowserTest extends DuskTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Suppress JavaScript console errors in browser that aren't actual test failures
        Browser::$storeConsoleLogAt = null;
        Browser::$storeSourceAt = null;

        // Create output directory if it doesn't exist
        if (! is_dir('tests/Browser/output')) {
            mkdir('tests/Browser/output', 0755, true);
        }
    }

    /**
     * Clean up after the test.
     */
    protected function tearDown(): void
    {
        // Ensure clean state for browser instances
        foreach (static::$browsers as $browser) {
            try {
                // Clear localStorage and sessionStorage
                $browser->driver->executeScript('window.localStorage.clear(); window.sessionStorage.clear();');
                // Delete all cookies
                $browser->driver->manage()->deleteAllCookies();
            } catch (\Exception $e) {
                // Ignore any errors during cleanup
            }
        }

        parent::tearDown();
    }

    /**
     * Capture page state for debugging
     */
    protected function captureDebugState(Browser $browser, string $prefix)
    {
        try {
            // Take screenshot
            $browser->screenshot("{$prefix}-debug");

            // Save page source
            file_put_contents(
                "tests/Browser/output/{$prefix}-source.html",
                $browser->driver->getPageSource()
            );

            // Log available selectors
            $availableElements = $browser->driver->executeScript('
                return {
                    tabCommentsLink: document.querySelector("a[href=\'#tab-comments\']") ? true : false,
                    tabComments: document.querySelector("#tab-comments") ? true : false,
                    commentItems: document.querySelectorAll(".comment-item").length,
                    replyButtons: document.querySelectorAll(".reply-button").length,
                    allLinks: Array.from(document.querySelectorAll("a")).map(a => a.textContent + " (" + a.href + ")")
                };
            ');

            file_put_contents(
                "tests/Browser/output/{$prefix}-elements.json",
                json_encode($availableElements, JSON_PRETTY_PRINT)
            );
        } catch (\Exception $e) {
            // Don't fail the test if debugging fails
            file_put_contents(
                "tests/Browser/output/{$prefix}-debug-error.log",
                $e->getMessage()."\n".$e->getTraceAsString()
            );
        }

        return $browser;
    }

    /**
     * Safely try to use JavaScript to interact with elements
     * Returns true if successful, false otherwise
     */
    protected function safeJsClick(Browser $browser, string $selector): bool
    {
        try {
            $result = $browser->driver->executeScript(
                "var el = document.querySelector('".$selector."'); ".
                'if (el) { el.click(); return true; } else { return false; }'
            );

            return (bool) $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Safely try to navigate directly to a URL instead of clicking links
     */
    protected function safeNavigation(Browser $browser, string $url)
    {
        try {
            $browser->visit($url);
            return true;
        } catch (\Exception $e) {
            \Log::warning("Safe navigation failed: {$e->getMessage()}");
            return false;
        }
    }
}
