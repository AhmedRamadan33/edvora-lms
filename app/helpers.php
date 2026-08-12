<?php

use App\Services\CurrencyService;
use Illuminate\Support\Str;

if (! function_exists('__status')) {
    /**
     * Translate a machine status value (paid, pending_review, ...).
     */
    function __status(?string $status): string
    {
        return __(Str::headline((string) $status));
    }
}

if (! function_exists('money')) {
    /**
     * Format an amount with the platform currency (or an explicit historical currency).
     */
    function money(float|int|string|null $amount, ?string $currency = null): string
    {
        return app(CurrencyService::class)->format($amount, $currency);
    }
}

if (! function_exists('platform_currency')) {
    function platform_currency(): string
    {
        return app(CurrencyService::class)->code();
    }
}
