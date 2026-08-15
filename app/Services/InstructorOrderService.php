<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InstructorOrderService
{
    public function __construct(private OrderRepository $orders)
    {
    }

    public function paginateForInstructor(User $instructor, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->orders->paginateForInstructor(
            $instructor->id,
            $perPage,
            $filters['search'] ?? null,
            $filters['payment_method'] ?? null,
            $filters['status'] ?? null,
        );
    }
}
