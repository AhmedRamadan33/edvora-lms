<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\SocialAuthService;
use Illuminate\Auth\Events\Registered;
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

        if ($user->wasRecentlyCreated) {
            event(new Registered($user));
        }

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
            ])->save();

            return $byEmail;
        }

        return User::create([
            'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'User',
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(40)),
            'avatar' => $socialUser->getAvatar(),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'locale' => session('locale', config('app.locale', 'en')),
        ]);
    }

    private function ensureProviderSupported(string $provider): void
    {
        if (! in_array($provider, self::PROVIDERS, true)) {
            throw new NotFoundHttpException;
        }
    }
}
