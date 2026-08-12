<?php

namespace App\Services;

use App\Models\Course;

class CurrencyService
{
    /**
     * Official platform currency (single-currency business model).
     */
    public function code(): string
    {
        return strtoupper((string) SettingService::currency());
    }

    /**
     * Keep every course row aligned with the platform currency.
     */
    public function syncCourses(?string $currency = null): int
    {
        $currency = strtoupper($currency ?: $this->code());

        return Course::query()
            ->where(function ($query) use ($currency) {
                $query->whereNull('currency')
                    ->orWhere('currency', '!=', $currency);
            })
            ->update(['currency' => $currency]);
    }

    public function format(float|int|string|null $amount, ?string $currency = null): string
    {
        return number_format((float) $amount, 2).' '.strtoupper($currency ?: $this->code());
    }
}
