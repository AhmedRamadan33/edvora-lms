<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::query()->updateOrCreate(
            ['code' => 'WELCOME20'],
            [
                'type' => 'percent',
                'value' => 20,
                'min_amount' => 20,
                'max_uses' => 500,
                'used_count' => 12,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ]
        );

        Coupon::query()->updateOrCreate(
            ['code' => 'FLAT10'],
            [
                'type' => 'fixed',
                'value' => 10,
                'min_amount' => 25,
                'max_uses' => 200,
                'used_count' => 4,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ]
        );

        Coupon::query()->updateOrCreate(
            ['code' => 'LEARN30'],
            [
                'type' => 'percent',
                'value' => 30,
                'min_amount' => 40,
                'max_uses' => 100,
                'used_count' => 1,
                'starts_at' => now()->subDays(2),
                'expires_at' => now()->addMonth(),
                'is_active' => true,
            ]
        );
    }
}
