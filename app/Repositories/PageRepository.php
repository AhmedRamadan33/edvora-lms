<?php

namespace App\Repositories;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageRepository extends BaseRepository
{
    public function __construct(Page $model)
    {
        parent::__construct($model);
    }

    public function paginateLatest(int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()->with('translations')->latest()->paginate($perPage);
    }
}
