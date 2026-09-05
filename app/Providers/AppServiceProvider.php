<?php

namespace App\Providers;

use App\Services\SettingService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('helpers.php');
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        $this->applySettingsDrivenMailConfig();
    }

    private function applySettingsDrivenMailConfig(): void
    {
        try {
            $host = SettingService::get('mail_host');
        } catch (\Throwable) {
            return;
        }

        if (blank($host)) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => SettingService::get('mail_port', 587),
            'mail.mailers.smtp.username' => SettingService::get('mail_username'),
            'mail.mailers.smtp.password' => SettingService::get('mail_password'),
            'mail.mailers.smtp.scheme' => SettingService::get('mail_encryption') === 'ssl' ? 'smtps' : null,
            'mail.from.address' => SettingService::get('mail_from_address') ?: config('mail.from.address'),
            'mail.from.name' => SettingService::get('mail_from_name') ?: config('mail.from.name'),
        ]);
    }
}
