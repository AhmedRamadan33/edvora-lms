<?php

namespace App\Services;

class SocialAuthService
{
    public function isEnabled(string $provider): bool
    {
        return (bool) SettingService::get("{$provider}_login_enabled", true) && $this->isConfigured($provider);
    }

    public function isConfigured(string $provider): bool
    {
        return filled($this->clientId($provider)) && filled($this->clientSecret($provider));
    }

    public function applyRuntimeConfig(string $provider): void
    {
        config([
            "services.{$provider}.client_id" => $this->clientId($provider),
            "services.{$provider}.client_secret" => $this->clientSecret($provider),
            "services.{$provider}.redirect" => config("services.{$provider}.redirect") ?: url("/auth/{$provider}/callback"),
        ]);
    }

    protected function clientId(string $provider): ?string
    {
        return config("services.{$provider}.client_id") ?: SettingService::get("{$provider}_client_id");
    }

    protected function clientSecret(string $provider): ?string
    {
        return config("services.{$provider}.client_secret") ?: SettingService::get("{$provider}_client_secret");
    }
}
