<?php

namespace App\Repositories;

use App\Models\CartItem;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Collection;

class CartRepository extends BaseRepository
{
    public function __construct(CartItem $model)
    {
        parent::__construct($model);
    }

    public function itemsForUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->with('course.instructor.instructorProfile')
            ->get();
    }

    public function clearForUser(int $userId): void
    {
        $this->query()->where('user_id', $userId)->delete();
    }

    public function findCouponByCode(string $code): ?Coupon
    {
        return Coupon::query()->where('code', strtoupper($code))->first();
    }
}
