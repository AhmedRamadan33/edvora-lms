<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'facebook'];

    public function __construct(private SocialAuthService $social)
    {
    }

    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureProviderSupported($provider);

        if (! $this->social->isEnabled($provider)) {
            return redirect()->route('login')->with('error', __(':provider sign-in is not available right now.', ['provider' => ucfirst($provider)]));
        }

        $this->social->applyRuntimeConfig($provider);

        $driver = Socialite::driver($provider);

        if ($provider === 'facebook') {
            $driver->scopes(['email']);
        }

        return $driver->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->ensureProviderSupported($provider);

        if (! $this->social->isEnabled($provider)) {
            return redirect()->route('login')->with('error', __(':provider sign-in is not available right now.', ['provider' => ucfirst($provider)]));
        }

        $this->social->applyRuntimeConfig($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable) {
            return redirect()->route('login')->with('error', __('Unable to sign in with :provider. Please try again.', ['provider' => ucfirst($provider)]));
        }

        if (! $socialUser->getEmail()) {
            return redirect()->route('login')->with('error', __(':provider did not share an email address. Please use a different sign-in method.', ['provider' => ucfirst($provider)]));
        }

        $user = $this->findOrCreateUser($provider, $socialUser);

        Auth::login($user, true);

        ActivityLog::record($user->wasRecentlyCreated ? 'auth.registered' : 'auth.login', $user);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function findOrCreateUser(string $provider, SocialiteUser $socialUser): User
    {
        $existing = User::query()
            ->where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existing) {
            return $existing;
        }

        $byEmail = $socialUser->getEmail()
            ? User::query()->where('email', $socialUser->getEmail())->first()
            : null;

        if ($byEmail) {
            $byEmail->forceFill([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'email_verified_at' => $byEmail->email_verified_at ?? now(),
            ])->save();

            return $byEmail;
        }

        $user = User::create([
            'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'User',
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(40)),
            'avatar' => $socialUser->getAvatar(),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'email_verified_at' => now(),
            'locale' => session('locale', config('app.locale', 'en')),
        ]);

        $user->syncRoles(['student']);

        return $user;
    }

    private function ensureProviderSupported(string $provider): void
    {
        if (! in_array($provider, self::PROVIDERS, true)) {
            throw new NotFoundHttpException;
        }
    }
}
