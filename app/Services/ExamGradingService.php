<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Repositories\ExamAttemptRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExamGradingService
{
    public function __construct(private ExamAttemptRepository $attempts)
    {
    }

    public function listForExam(Exam $exam, array $filters): LengthAwarePaginator
    {
        return $this->attempts->paginateForExam($exam, $filters);
    }

    public function findForGrading(Exam $exam, int $attemptId): ExamAttempt
    {
        $attempt = $this->attempts->findForExam($exam, $attemptId);

        abort_unless($attempt, 404);

        return $attempt;
    }

    public function grade(ExamAttempt $attempt, User $instructor, array $gradedAnswers): ExamAttempt
    {
        abort_unless(in_array($attempt->status, [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_GRADED], true), 403);

        return DB::transaction(function () use ($attempt, $instructor, $gradedAnswers) {
            $attempt->load('answers.bankQuestion', 'exam');

            foreach ($gradedAnswers as $row) {
                $answer = $attempt->answers->firstWhere('bank_question_id', (int) $row['bank_question_id']);

                if (! $answer || $answer->bankQuestion->isAutoGraded()) {
                    continue;
                }

                $points = (int) $row['points_awarded'];

                $answer->update([
                    'points_awarded' => $points,
                    'is_correct' => $points >= $answer->bankQuestion->points,
                    'instructor_feedback' => $row['instructor_feedback'] ?? null,
                ]);
            }

            $attempt->load('answers');
            $stillPending = $attempt->answers->contains(fn ($answer) => $answer->points_awarded === null);
            $autoScore = (int) $attempt->answers->sum('points_awarded');

            $passed = $stillPending
                ? null
                : ($attempt->total_points > 0 && (($autoScore / $attempt->total_points) * 100) >= $attempt->exam->pass_percent);

            $attempt->update([
                'auto_score' => $autoScore,
                'status' => $stillPending ? ExamAttempt::STATUS_SUBMITTED : ExamAttempt::STATUS_GRADED,
                'passed' => $passed,
                'reviewed_at' => now(),
                'reviewed_by' => $instructor->id,
            ]);

            $attempt = $attempt->fresh('answers.bankQuestion.choices', 'answers.bankQuestion.matches', 'user', 'exam', 'reviewer');

            ActivityLog::record('exam_attempt.graded', $attempt, [
                'exam' => $attempt->exam?->title,
                'student' => $attempt->user?->name,
            ]);

            if (! $stillPending) {
                $attempt->user?->notify(new GenericNotification(
                    __('Your exam ":exam" was graded. You :result.', [
                        'exam' => $attempt->exam?->title,
                        'result' => $passed ? __('passed') : __('did not pass'),
                    ]),
                    route('exams.result', $attempt->exam),
                    __('Exam graded')
                ));
            }

            return $attempt;
        });
    }

    public function pendingReviewCount(User $instructor): int
    {
        return $this->attempts->pendingReviewCountForInstructor($instructor->id);
    }

    public function recentActivity(User $instructor, int $limit = 5): Collection
    {
        return $this->attempts->recentForInstructor($instructor->id, $limit);
    }
}
