<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function paginateForAdmin(
        int $perPage = 20,
        ?string $search = null,
        ?string $paymentMethod = null,
        ?string $status = null,
    ): LengthAwarePaginator {
        return $this->query()
            ->with(['user', 'payment'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($paymentMethod, fn ($query) => $query->where('payment_method', $paymentMethod))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($orderQuery) use ($search) {
                    $orderQuery
                        ->where('number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForInstructor(
        int $instructorId,
        int $perPage = 20,
        ?string $search = null,
        ?string $paymentMethod = null,
        ?string $status = null,
    ): LengthAwarePaginator {
        return $this->query()
            ->with(['user', 'payment'])
            ->whereHas('items', fn ($query) => $query->where('instructor_id', $instructorId))
            ->withSum(
                ['items as instructor_amount' => fn ($query) => $query->where('instructor_id', $instructorId)],
                'instructor_earning'
            )
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($paymentMethod, fn ($query) => $query->where('payment_method', $paymentMethod))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($orderQuery) use ($search) {
                    $orderQuery
                        ->where('number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
