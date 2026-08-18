<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return Setting::getValue($key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        Setting::setValue($key, $value);
    }

    public static function many(array $keys): array
    {
        $result = [];
        foreach ($keys as $key => $default) {
            if (is_int($key)) {
                $result[$default] = static::get($default);
            } else {
                $result[$key] = static::get($key, $default);
            }
        }

        return $result;
    }

    public static function commissionRate(): float
    {
        return (float) static::get('default_commission', config('edvora.default_commission', 20));
    }

    public static function currency(): string
    {
        return strtoupper((string) static::get('currency', config('edvora.currency', 'USD')));
    }

    public static function platformName(): string
    {
        return (string) static::get('platform_name', config('app.name', 'Edvora'));
    }

    public static function platformEmail(): string
    {
        return (string) static::get('platform_email', config('mail.from.address', 'support@edvora.test'));
    }

    public static function platformPhone(): string
    {
        return (string) static::get('platform_phone', '+01199676020');
    }
}
