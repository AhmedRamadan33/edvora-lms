<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
            UserSeeder::class,
            InstructorProfileSeeder::class,
            CategorySeeder::class,
            CourseSeeder::class,
            CouponSeeder::class,
            PageSeeder::class,
            TestimonialSeeder::class,
            DemoCommerceSeeder::class,
        ]);
    }
}
