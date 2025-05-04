<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     *
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect(string $provider)
    {
        // Validate provider
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle callback from provider.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function callback(string $provider)
    {
        // Validate provider
        $this->validateProvider($provider);

        try {
            $providerUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'error' => 'An error occurred while trying to authenticate with '.$provider.'. Please try again.',
            ]);
        }

        // Check if user exists with this provider id
        $user = User::where("$provider".'_id', $providerUser->getId())
            ->orWhere('email', $providerUser->getEmail())
            ->first();

        // Create user if it doesn't exist
        if (! $user) {
            $user = $this->createUser($providerUser, $provider);
        } elseif (! $user->{$provider.'_id'}) {
            // Update provider ID if user exists but doesn't have this provider id
            $user->{$provider.'_id'} = $providerUser->getId();
            $user->save();
        }

        // Log in the user
        Auth::login($user);

        return redirect()->intended('/dashboard');
    }

    /**
     * Create a new user with provider data.
     *
     * @param  \Laravel\Socialite\Contracts\User  $providerUser
     * @return \App\Models\User
     */
    protected function createUser($providerUser, string $provider)
    {
        $user = new User;
        $user->name = $providerUser->getName() ?? $providerUser->getNickname();
        $user->email = $providerUser->getEmail();
        $user->email_verified_at = now();
        $user->password = Hash::make(md5(uniqid().time())); // Generate a random password
        $user->{$provider.'_id'} = $providerUser->getId();
        $user->save();

        return $user;
    }

    /**
     * Validate provider name.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function validateProvider(string $provider)
    {
        if (! in_array($provider, ['github', 'google'])) {
            throw new \InvalidArgumentException("Invalid provider [{$provider}].");
        }
    }
}
