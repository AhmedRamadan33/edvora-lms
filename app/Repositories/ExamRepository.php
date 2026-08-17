<?php

namespace App\Repositories;

use App\Models\Exam;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ExamRepository extends BaseRepository
{
    public function __construct(Exam $model)
    {
        parent::__construct($model);
    }

    public function paginateForInstructor(int $instructorId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->whereHas('course', fn ($query) => $query->where('instructor_id', $instructorId))
            ->withCount('examQuestions')
            ->with('course.translations')
            ->when($filters['course_id'] ?? null, fn ($query, $courseId) => $query->where('course_id', $courseId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
