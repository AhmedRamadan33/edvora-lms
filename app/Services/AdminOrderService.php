<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminOrderService
{
    public function __construct(private OrderRepository $orders)
    {
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->orders->paginateForAdmin(
            $perPage,
            $filters['search'] ?? null,
            $filters['payment_method'] ?? null,
            $filters['status'] ?? null,
        );
    }
}
