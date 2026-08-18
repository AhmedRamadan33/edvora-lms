<?php

namespace App\Services;

use App\Models\BankQuestion;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Repositories\ExamAttemptRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentExamService
{
    public function __construct(private ExamAttemptRepository $attempts)
    {
    }

    public function availableForStudent(User $student): Collection
    {
        $courseIds = $student->enrollments()->pluck('course_id');

        return Exam::query()
            ->whereIn('course_id', $courseIds)
            ->where('status', 'published')
            ->with('course.translations')
            ->withCount('examQuestions')
            ->latest()
            ->get()
            ->map(function (Exam $exam) use ($student) {
                $exam->setAttribute('attempt', $this->attempts->forExamAndUser($exam->id, $student->id));

                return $exam;
            });
    }

    public function startAttempt(Exam $exam, User $student): ExamAttempt
    {
        $this->ensureAccessible($exam, $student);

        abort_if($this->attempts->forExamAndUser($exam->id, $student->id), 403, __('You have already attempted this exam.'));

        return $this->attempts->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now(),
            'total_points' => (int) $exam->questions()->sum('points'),
        ]);
    }

    public function submitAttempt(ExamAttempt $attempt, array $answers): ExamAttempt
    {
        abort_unless($attempt->status === ExamAttempt::STATUS_IN_PROGRESS, 403);

        return DB::transaction(function () use ($attempt, $answers) {
            $questions = $attempt->exam->questions()->with('choices', 'matches')->get();
            $autoScore = 0;
            $hasPending = false;

            foreach ($questions as $question) {
                $graded = $this->gradeQuestion($question, $answers[$question->id] ?? null);

                $attempt->answers()->create([
                    'bank_question_id' => $question->id,
                    'answer_data' => $graded['answer_data'],
                    'is_correct' => $graded['is_correct'],
                    'points_awarded' => $graded['points_awarded'],
                ]);

                if ($graded['points_awarded'] !== null) {
                    $autoScore += $graded['points_awarded'];
                } else {
                    $hasPending = true;
                }
            }

            $passed = $hasPending
                ? null
                : ($attempt->total_points > 0 && (($autoScore / $attempt->total_points) * 100) >= $attempt->exam->pass_percent);

            $this->attempts->update($attempt, [
                'submitted_at' => now(),
                'status' => $hasPending ? ExamAttempt::STATUS_SUBMITTED : ExamAttempt::STATUS_GRADED,
                'auto_score' => $autoScore,
                'passed' => $passed,
            ]);

            return $attempt->fresh('answers.bankQuestion.choices', 'answers.bankQuestion.matches', 'answers.bankQuestion.subject');
        });
    }

    public function resultFor(ExamAttempt $attempt): array
    {
        $attempt->load('exam', 'answers.bankQuestion.choices', 'answers.bankQuestion.matches', 'answers.bankQuestion.subject');

        return [
            'attempt' => $attempt,
            'answers' => $attempt->answers,
        ];
    }

    public function ensureAccessible(Exam $exam, User $student): void
    {
        abort_unless($exam->status === 'published', 404);
        abort_unless($student->isEnrolledIn($exam->course_id) || $student->hasRole('admin'), 403);
    }

    protected function gradeQuestion(BankQuestion $question, mixed $rawAnswer): array
    {
        return match ($question->type) {
            'mcq_single', 'true_false' => $this->gradeChoice($question, $rawAnswer),
            'matching' => $this->gradeMatching($question, $rawAnswer),
            default => [
                'answer_data' => ['text' => is_string($rawAnswer) ? trim($rawAnswer) : null],
                'is_correct' => null,
                'points_awarded' => null,
            ],
        };
    }

    protected function gradeChoice(BankQuestion $question, mixed $rawAnswer): array
    {
        $choiceId = is_numeric($rawAnswer) ? (int) $rawAnswer : null;
        $correctChoice = $question->choices->firstWhere('is_correct', true);
        $isCorrect = $choiceId !== null && $correctChoice && $choiceId === $correctChoice->id;

        return [
            'answer_data' => ['choice_id' => $choiceId],
            'is_correct' => $isCorrect,
            'points_awarded' => $isCorrect ? $question->points : 0,
        ];
    }

    protected function gradeMatching(BankQuestion $question, mixed $rawAnswer): array
    {
        $submitted = is_array($rawAnswer) ? $rawAnswer : [];
        $matches = $question->matches;
        $total = max($matches->count(), 1);
        $correct = 0;

        foreach ($matches as $match) {
            $selected = $submitted[$match->id] ?? null;
            if ($selected !== null && trim((string) $selected) === trim($match->match_text)) {
                $correct++;
            }
        }

        return [
            'answer_data' => ['pairs' => $submitted],
            'is_correct' => $correct === $total,
            'points_awarded' => (int) round($question->points * ($correct / $total)),
        ];
    }
}
