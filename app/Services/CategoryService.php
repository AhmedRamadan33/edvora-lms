<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(private CategoryRepository $categories)
    {
    }

    public function paginate(int $perPage = 20, ?string $search = null)
    {
        return $this->categories->paginateOrdered($perPage, $search);
    }

    public function create(array $data, bool $isActive = true): Category
    {
        /** @var Category $category */
        $category = $this->categories->create([
            'slug' => Str::slug($data['name_en']).'-'.Str::random(4),
            'icon' => $data['icon'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $isActive,
        ]);

        $this->syncTranslations($category, $data);

        ActivityLog::record('category.created', $category, ['name' => $data['name_en']]);

        return $category;
    }

    public function update(Category $category, array $data, bool $isActive = true): Category
    {
        $this->categories->update($category, [
            'icon' => $data['icon'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $isActive,
        ]);

        $this->syncTranslations($category, $data);

        ActivityLog::record('category.updated', $category, ['name' => $data['name_en']]);

        return $category->refresh();
    }

    public function delete(Category $category): bool
    {
        $name = $category->translation()?->name;

        $result = $this->categories->delete($category);

        ActivityLog::record('category.deleted', $category, ['name' => $name]);

        return $result;
    }

    protected function syncTranslations(Category $category, array $data): void
    {
        foreach (['en', 'ar'] as $locale) {
            $category->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'name' => $data["name_{$locale}"],
                    'description' => $data["description_{$locale}"] ?? null,
                ]
            );
        }
    }
}
