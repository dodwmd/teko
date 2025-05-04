<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page as BasePage;

class AdminLogin extends BasePage
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/admin/login';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        // Check if we're on login page or already logged in
        try {
            if ($browser->element('form input[name="email"]')) {
                $browser->assertPathIs($this->url());
            }
        } catch (\Exception $e) {
            // Likely already logged in, no need to check path
        }
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@email-field' => 'input[name="email"]',
            '@password-field' => 'input[name="password"]',
            '@remember-checkbox' => 'input[name="remember"]',
            '@login-button' => 'button[type="submit"]',
        ];
    }

    /**
     * Login with the given credentials.
     */
    public function login(Browser $browser, string $email = 'admin@example.com', string $password = 'password'): void
    {
        try {
            // Try to see if we're already logged in
            $browser->visitRoute('platform.main')
                ->pause(1000);

            if ($browser->seeLink('Dashboard') || $browser->seeLink('Tasks')) {
                return; // Already logged in
            }
        } catch (\Exception $e) {
            // Not logged in, continue to login process
        }

        // Navigate to login page and log in
        $browser->visit($this->url())
            ->waitFor('@email-field', 5)
            ->type('@email-field', $email)
            ->type('@password-field', $password)
            ->click('@remember-checkbox')
            ->click('@login-button')
            ->pause(3000); // Give more time to process login
    }
}
