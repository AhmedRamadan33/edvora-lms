<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\BankQuestion;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Subject;
use App\Models\User;
use App\Repositories\ExamRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExamService
{
    public function __construct(private ExamRepository $exams)
    {
    }

    public function listForInstructor(User $instructor, array $filters): LengthAwarePaginator
    {
        return $this->exams->paginateForInstructor($instructor->id, $filters);
    }

    /**
     * Creates the exam and fills it with randomly picked, active bank questions per rule.
     * When a rule asks for more questions than are actually available, every available
     * question for that rule is added instead of rejecting the whole request; the shortfall
     * is returned so the caller can tell the instructor exactly what happened.
     *
     * @return array{exam: Exam, shortfalls: array<int, array{subject: string, type: string, requested: int, added: int}>}
     */
    public function create(Course $course, User $instructor, array $data): array
    {
        return DB::transaction(function () use ($course, $instructor, $data) {
            $exam = $this->exams->create([
                'course_id' => $course->id,
                'created_by' => $instructor->id,
                'title' => $data['title'],
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'pass_percent' => $data['pass_percent'] ?? 60,
                'status' => 'draft',
            ]);

            $shortfalls = $this->applyRules($exam, $course, $data['rules']);

            ActivityLog::record('exam.created', $exam, ['title' => $exam->title, 'course' => $course->translation()?->title]);

            return [
                'exam' => $exam->load('examQuestions.bankQuestion'),
                'shortfalls' => $shortfalls,
            ];
        });
    }

    public function update(Exam $exam, array $data): Exam
    {
        $exam = $this->exams->update($exam, [
            'title' => $data['title'],
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'pass_percent' => $data['pass_percent'] ?? $exam->pass_percent,
        ]);

        ActivityLog::record('exam.updated', $exam, ['title' => $exam->title]);

        return $exam;
    }

    /**
     * Appends more randomly picked questions to an existing exam, same shortfall-reporting behavior as create().
     *
     * @return array<int, array{subject: string, type: string, requested: int, added: int}>
     */
    public function addQuestions(Exam $exam, array $rules): array
    {
        $before = $exam->examQuestions()->count();

        $shortfalls = DB::transaction(fn () => $this->applyRules($exam, $exam->course, $rules));

        $added = $exam->examQuestions()->count() - $before;
        ActivityLog::record('exam.questions_added', $exam, ['title' => $exam->title, 'count' => $added]);

        return $shortfalls;
    }

    public function removeQuestion(Exam $exam, ExamQuestion $examQuestion): void
    {
        abort_unless($examQuestion->exam_id === $exam->id, 404);

        $examQuestion->delete();

        ActivityLog::record('exam.question_removed', $exam, ['title' => $exam->title]);
    }

    public function paginateQuestions(Exam $exam, int $perPage = 15): LengthAwarePaginator
    {
        return $exam->examQuestions()
            ->with('bankQuestion.subject', 'bankQuestion.choices', 'bankQuestion.matches')
            ->paginate($perPage);
    }

    public function toggleStatus(Exam $exam): Exam
    {
        $exam = $this->exams->update($exam, [
            'status' => $exam->status === 'published' ? 'draft' : 'published',
        ]);

        ActivityLog::record('exam.status_toggled', $exam, ['title' => $exam->title, 'status' => $exam->status]);

        return $exam;
    }

    public function delete(Exam $exam): void
    {
        $title = $exam->title;

        $this->exams->delete($exam);

        ActivityLog::record('exam.deleted', $exam, ['title' => $title]);
    }

    /**
     * @return array<int, array{subject: string, type: string, requested: int, added: int}>
     */
    protected function applyRules(Exam $exam, Course $course, array $rules): array
    {
        $pickedIds = $exam->examQuestions()->pluck('bank_question_id')->all();
        $sortOrder = (int) $exam->examQuestions()->max('sort_order');
        $shortfalls = [];

        foreach ($rules as $rule) {
            $requested = (int) $rule['count'];

            $questions = BankQuestion::query()
                ->where('course_id', $course->id)
                ->where('subject_id', $rule['subject_id'])
                ->where('type', $rule['type'])
                ->where('is_active', true)
                ->whereNotIn('id', $pickedIds)
                ->inRandomOrder()
                ->limit($requested)
                ->pluck('id');

            foreach ($questions as $questionId) {
                $pickedIds[] = $questionId;
                $exam->examQuestions()->create([
                    'bank_question_id' => $questionId,
                    'sort_order' => ++$sortOrder,
                ]);
            }

            if ($questions->count() < $requested) {
                $shortfalls[] = [
                    'subject' => Subject::query()->find($rule['subject_id'])?->name ?? (string) $rule['subject_id'],
                    'type' => $rule['type'],
                    'requested' => $requested,
                    'added' => $questions->count(),
                ];
            }
        }

        return $shortfalls;
    }
}
