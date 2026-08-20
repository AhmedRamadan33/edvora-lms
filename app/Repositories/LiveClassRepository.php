<?php

namespace App\Repositories;

use App\Models\LiveClass;
use Illuminate\Database\Eloquent\Collection;

class LiveClassRepository extends BaseRepository
{
    public function __construct(LiveClass $model)
    {
        parent::__construct($model);
    }

    public function forCourse(int $courseId): Collection
    {
        return $this->query()
            ->where('course_id', $courseId)
            ->orderBy('scheduled_at')
            ->get();
    }

    public function forInstructor(int $instructorId): Collection
    {
        return $this->query()
            ->with('course.translations')
            ->where('instructor_id', $instructorId)
            ->orderByDesc('scheduled_at')
            ->get();
    }

    public function upcomingForCourse(int $courseId): Collection
    {
        return $this->query()
            ->where('course_id', $courseId)
            ->where('status', LiveClass::STATUS_SCHEDULED)
            ->where('scheduled_at', '>=', now()->subMinutes(10))
            ->orderBy('scheduled_at')
            ->get();
    }

    public function upcomingForStudent(int $studentId, int $limit = 5): Collection
    {
        return $this->query()
            ->with('course.translations')
            ->whereHas('course.enrollments', fn ($q) => $q->where('user_id', $studentId))
            ->where('status', LiveClass::STATUS_SCHEDULED)
            ->where('scheduled_at', '>=', now()->subMinutes(10))
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();
    }

    public function dueForReminder(): Collection
    {
        return $this->query()
            ->where('status', LiveClass::STATUS_SCHEDULED)
            ->whereNull('reminder_sent_at')
            ->whereBetween('scheduled_at', [now()->addMinutes(14), now()->addMinutes(15)])
            ->get();
    }
}
