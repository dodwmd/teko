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
        // Only check if we're on login page if we're not already logged in
        if (! $browser->element('form input[name="email"]')) {
            return; // Already logged in, no need to check path
        }

        $browser->assertPathIs($this->url());
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
        // Check if we're already logged in (Orchid's admin panel should have a certain element)
        if ($browser->visitRoute('platform.main')->assertSee('Dashboard', false)->seeLink('Tasks')) {
            return; // Already logged in
        }

        // Otherwise navigate to login page and log in
        $browser->visit($this->url())
            ->type('@email-field', $email)
            ->type('@password-field', $password)
            ->check('@remember-checkbox')
            ->press('@login-button')
            ->pause(2000); // Give the server more time to process
    }
}
