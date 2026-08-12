<?php

namespace App\Repositories;

use App\Models\Testimonial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TestimonialRepository extends BaseRepository
{
    public function __construct(Testimonial $model)
    {
        parent::__construct($model);
    }

    public function paginateOrdered(int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()->orderBy('sort_order')->latest()->paginate($perPage);
    }

    public function publishedForHome(int $limit = 6): Collection
    {
        return $this->query()
            ->where('is_published', true)
            ->where('show_on_home', true)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    public function publishedAll()
    {
        return $this->query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    public function publishedPaginated(int $perPage = 12): LengthAwarePaginator
    {
        return $this->query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->latest()
            ->paginate($perPage);
    }
}
