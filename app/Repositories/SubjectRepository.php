<?php

namespace App\Repositories;

use App\Models\Subject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SubjectRepository extends BaseRepository
{
    public function __construct(Subject $model)
    {
        parent::__construct($model);
    }

    public function forCourse(int $courseId): Collection
    {
        return $this->query()
            ->where('course_id', $courseId)
            ->orderBy('name')
            ->get();
    }

    public function paginateForInstructor(int $instructorId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->whereHas('course', fn ($query) => $query->where('instructor_id', $instructorId))
            ->withCount('bankQuestions')
            ->with('course.translations')
            ->when($filters['course_id'] ?? null, fn ($query, $courseId) => $query->where('course_id', $courseId))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
