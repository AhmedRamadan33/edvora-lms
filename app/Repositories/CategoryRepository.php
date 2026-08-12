<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository extends BaseRepository
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function paginateOrdered(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        return $this->query()
            ->with('translations')
            ->when($search, fn ($query) => $query->whereHas('translations', fn ($translationQuery) => $translationQuery->where('name', 'like', "%{$search}%")))
            ->orderBy('sort_order')
            ->paginate($perPage)
            ->withQueryString();
    }
}
