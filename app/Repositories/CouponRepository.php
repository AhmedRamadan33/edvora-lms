<?php

namespace App\Repositories;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CouponRepository extends BaseRepository
{
    public function __construct(Coupon $model)
    {
        parent::__construct($model);
    }

    public function paginateLatest(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        return $this->query()
            ->when($search, fn ($query) => $query->where('code', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
