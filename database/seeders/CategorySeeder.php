<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Seeders\Data\CourseCatalog;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (CourseCatalog::categories() as $index => $item) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'icon' => $item['icon'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );

            $category->translations()->updateOrCreate(['locale' => 'en'], [
                'name' => $item['en'],
                'description' => $item['desc_en'],
            ]);
            $category->translations()->updateOrCreate(['locale' => 'ar'], [
                'name' => $item['ar'],
                'description' => $item['desc_ar'],
            ]);
        }
    }
}
