<?php

namespace App\Repositories;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewRepository extends BaseRepository
{
    public function __construct(Review $model)
    {
        parent::__construct($model);
    }

    public function updateOrCreateForUserAndCourse(int $userId, int $courseId, array $data): Review
    {
        return $this->query()->updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            $data
        );
    }

    public function findOwnedByUser(int $courseId, int $userId): ?Review
    {
        return $this->query()
            ->where('course_id', $courseId)
            ->where('user_id', $userId)
            ->first();
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->with('user', 'course.translations')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['course_id'] ?? null, fn ($query, $courseId) => $query->where('course_id', $courseId))
            ->when($filters['rating'] ?? null, fn ($query, $rating) => $query->where('rating', $rating))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->whereHas('user', fn ($user) => $user
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
