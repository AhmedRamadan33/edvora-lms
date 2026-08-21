<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\BankQuestion;
use App\Models\Course;
use App\Models\User;
use App\Repositories\BankQuestionRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class BankQuestionService
{
    public function __construct(private BankQuestionRepository $questions)
    {
    }

    public function listForCourse(Course $course, array $filters): array
    {
        return [
            'items' => $this->questions->paginateForCourse($course->id, $filters),
            'stats' => $this->questions->countsByType($course->id),
        ];
    }

    public function create(Course $course, User $instructor, array $data, ?UploadedFile $image = null): BankQuestion
    {
        return DB::transaction(function () use ($course, $instructor, $data, $image) {
            $question = $this->questions->create([
                'course_id' => $course->id,
                'subject_id' => $data['subject_id'] ?? null,
                'created_by' => $instructor->id,
                'type' => $data['type'],
                'question' => $data['question'],
                'image' => $image?->store('question-bank', 'public'),
                'difficulty' => $data['difficulty'] ?? 'medium',
                'points' => $data['points'] ?? 1,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
                'sort_order' => $course->bankQuestions()->count() + 1,
            ]);

            $this->syncAnswers($question, $data);

            ActivityLog::record('bank_question.created', $question, ['course' => $course->translation()?->title]);

            return $question->load('choices', 'matches', 'subject');
        });
    }

    public function update(BankQuestion $question, array $data, ?UploadedFile $image = null): BankQuestion
    {
        return DB::transaction(function () use ($question, $data, $image) {
            $this->questions->update($question, [
                'subject_id' => $data['subject_id'] ?? null,
                'type' => $data['type'],
                'question' => $data['question'],
                'image' => $image ? $image->store('question-bank', 'public') : $question->image,
                'difficulty' => $data['difficulty'] ?? 'medium',
                'points' => $data['points'] ?? 1,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $question->is_active,
            ]);

            $previousChoiceImages = $question->choices()->orderBy('sort_order')->pluck('image')->values()->all();

            $question->choices()->delete();
            $question->matches()->delete();
            $this->syncAnswers($question, $data, $previousChoiceImages);

            ActivityLog::record('bank_question.updated', $question, ['course' => $question->course?->translation()?->title]);

            return $question->load('choices', 'matches', 'subject');
        });
    }

    public function delete(BankQuestion $question): void
    {
        $course = $question->course;

        $this->questions->delete($question);

        ActivityLog::record('bank_question.deleted', $question, ['course' => $course?->translation()?->title]);
    }

    public function toggleActive(BankQuestion $question): BankQuestion
    {
        $question = $this->questions->update($question, ['is_active' => ! $question->is_active]);

        ActivityLog::record('bank_question.toggled', $question, ['course' => $question->course?->translation()?->title, 'active' => $question->is_active]);

        return $question;
    }

    protected function syncAnswers(BankQuestion $question, array $data, array $previousChoiceImages = []): void
    {
        match ($data['type']) {
            'mcq_single' => $this->syncChoices($question, $data['choices'] ?? [], $previousChoiceImages),
            'true_false' => $this->syncTrueFalse($question, $data['true_false_answer'] ?? 'true'),
            'matching' => $this->syncMatches($question, $data['matches'] ?? []),
            // essay and fill_blank have no predefined answer - the instructor grades them manually.
            default => null,
        };
    }

    protected function syncChoices(BankQuestion $question, array $choices, array $previousImages = []): void
    {
        $position = 0;
        foreach ($choices as $choice) {
            $text = trim($choice['text'] ?? '');
            if ($text === '') {
                continue;
            }

            $image = $choice['image'] ?? null;
            $imagePath = $image instanceof UploadedFile
                ? $image->store('question-bank/choices', 'public')
                : ($previousImages[$position] ?? null);

            $question->choices()->create([
                'text' => $text,
                'is_correct' => (bool) ($choice['is_correct'] ?? false),
                'image' => $imagePath,
                'sort_order' => $position + 1,
            ]);

            $position++;
        }
    }

    protected function syncTrueFalse(BankQuestion $question, string $correctAnswer): void
    {
        $question->choices()->create(['text' => 'True', 'is_correct' => $correctAnswer === 'true', 'sort_order' => 1]);
        $question->choices()->create(['text' => 'False', 'is_correct' => $correctAnswer === 'false', 'sort_order' => 2]);
    }

    protected function syncMatches(BankQuestion $question, array $matches): void
    {
        $index = 0;
        foreach ($matches as $match) {
            $prompt = trim($match['prompt'] ?? '');
            $answer = trim($match['match'] ?? '');
            if ($prompt === '' || $answer === '') {
                continue;
            }

            $question->matches()->create([
                'prompt_text' => $prompt,
                'match_text' => $answer,
                'sort_order' => ++$index,
            ]);
        }
    }
}
