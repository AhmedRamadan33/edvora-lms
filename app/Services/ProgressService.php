<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;

class ProgressService
{
    public function __construct(private CertificateService $certificates) {}

    /**
     * @return array{progress: LessonProgress, justCompletedCourse: bool}
     */
    public function markCompleted(User $user, Lesson $lesson, int $position = 0): array
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

        $justCompletedCourse = $this->recalculate($user->id, $lesson->course_id);

        ActivityLog::record('lesson.completed', $lesson, [
            'title' => $lesson->title,
            'course' => $lesson->course?->translation()?->title,
        ]);

        return ['progress' => $progress, 'justCompletedCourse' => $justCompletedCourse];
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

    public function recalculate(int $userId, int $courseId): bool
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
            return false;
        }

        $wasCompleted = (bool) $enrollment->completed_at;

        $enrollment->update([
            'progress_percent' => $percent,
            'completed_at' => $percent >= 100 ? ($enrollment->completed_at ?? now()) : null,
        ]);

        if ($percent >= 100) {
            $this->certificates->issueIfEligible($enrollment->user, $enrollment->course);
        }

        return ! $wasCompleted && $percent >= 100;
    }
}
