<?php

namespace App\Repositories;

use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CourseRepository extends BaseRepository
{
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    public function paginateForInstructor(int $instructorId, int $perPage = 12, ?string $search = null): LengthAwarePaginator
    {
        return $this->query()
            ->where('instructor_id', $instructorId)
            ->with(['translations', 'category.translations'])
            ->when($search, fn ($query) => $query->whereHas('translations', fn ($translationQuery) => $translationQuery->where('title', 'like', "%{$search}%")))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function activeCategories(): Collection
    {
        return \App\Models\Category::query()
            ->where('is_active', true)
            ->with('translations')
            ->get();
    }

    public function loadCurriculum(Course $course): Course
    {
        return $course->load(['translations', 'sections.lessons.video', 'sections.lessons.quiz.questions']);
    }
}
