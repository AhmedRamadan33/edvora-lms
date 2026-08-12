<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;

class ProgressService
{
    public function __construct(private CertificateService $certificates) {}

    public function markCompleted(User $user, Lesson $lesson, int $position = 0): LessonProgress
    {
        $progress = LessonProgress::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'course_id' => $lesson->course_id,
                'last_position_seconds' => $position,
                'is_completed' => true,
                'completed_at' => now(),
            ]
        );

        $this->recalculate($user->id, $lesson->course_id);

        return $progress;
    }

    public function updatePosition(User $user, Lesson $lesson, int $position): LessonProgress
    {
        return LessonProgress::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'course_id' => $lesson->course_id,
                'last_position_seconds' => $position,
            ]
        );
    }

    public function recalculate(int $userId, int $courseId): void
    {
        $total = Lesson::query()->where('course_id', $courseId)->where('is_published', true)->count();
        $completed = LessonProgress::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('is_completed', true)
            ->count();

        $percent = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

        $enrollment = Enrollment::query()
            ->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (! $enrollment) {
            return;
        }

        $enrollment->update([
            'progress_percent' => $percent,
            'completed_at' => $percent >= 100 ? ($enrollment->completed_at ?? now()) : null,
        ]);

        if ($percent >= 100) {
            $this->certificates->issueIfEligible($enrollment->user, $enrollment->course);
        }
    }
}
