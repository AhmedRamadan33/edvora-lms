<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Command;

class SyncPlatformCurrencyCommand extends Command
{
    protected $signature = 'edvora:sync-currency {currency? : Optional currency code such as EGP or USD}';

    protected $description = 'Align all course currencies with the official platform currency';

    public function handle(CurrencyService $currency): int
    {
        $code = $this->argument('currency')
            ? strtoupper((string) $this->argument('currency'))
            : $currency->code();

        if ($this->argument('currency')) {
            \App\Services\SettingService::set('currency', $code);
        }

        $updated = $currency->syncCourses($code);

        $this->info("Platform currency: {$code}");
        $this->info("Courses updated: {$updated}");

        return self::SUCCESS;
    }
}
