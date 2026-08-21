<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Page;
use App\Repositories\PageRepository;
use Illuminate\Support\Str;

class PageService
{
    public function __construct(private PageRepository $pages)
    {
    }

    public function paginate(int $perPage = 20)
    {
        return $this->pages->paginateLatest($perPage);
    }

    public function create(array $data): Page
    {
        /** @var Page $page */
        $page = $this->pages->create([
            'slug' => $data['slug'] ?: Str::slug($data['title_en']),
            'is_published' => true,
        ]);

        $this->syncTranslations($page, $data);

        ActivityLog::record('page.created', $page, ['title' => $data['title_en']]);

        return $page;
    }

    public function update(Page $page, array $data, bool $isPublished = true): Page
    {
        $this->pages->update($page, ['is_published' => $isPublished]);
        $this->syncTranslations($page, $data);

        ActivityLog::record('page.updated', $page, ['title' => $data['title_en']]);

        return $page->refresh();
    }

    protected function syncTranslations(Page $page, array $data): void
    {
        foreach (['en', 'ar'] as $locale) {
            $page->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $data["title_{$locale}"],
                    'body' => $data["body_{$locale}"] ?? null,
                ]
            );
        }
    }
}
