<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ExamAttemptRepository extends BaseRepository
{
    public function __construct(ExamAttempt $model)
    {
        parent::__construct($model);
    }

    public function forExamAndUser(int $examId, int $userId): ?ExamAttempt
    {
        return $this->query()
            ->where('exam_id', $examId)
            ->where('user_id', $userId)
            ->first();
    }

    public function paginateForExam(Exam $exam, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->where('exam_id', $exam->id)
            ->with('user')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->whereHas('user', fn ($user) => $user
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->latest('submitted_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findForExam(Exam $exam, int $attemptId): ?ExamAttempt
    {
        return $this->query()
            ->where('exam_id', $exam->id)
            ->with('user', 'exam', 'reviewer', 'answers.bankQuestion.choices', 'answers.bankQuestion.matches')
            ->find($attemptId);
    }

    public function pendingReviewCountForInstructor(int $instructorId): int
    {
        return $this->query()
            ->where('status', ExamAttempt::STATUS_SUBMITTED)
            ->whereHas('exam.course', fn ($query) => $query->where('instructor_id', $instructorId))
            ->count();
    }

    public function recentForInstructor(int $instructorId, int $limit = 5): Collection
    {
        return $this->query()
            ->whereHas('exam.course', fn ($query) => $query->where('instructor_id', $instructorId))
            ->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_GRADED])
            ->with('user', 'exam.course.translations')
            ->latest('submitted_at')
            ->take($limit)
            ->get();
    }
}
