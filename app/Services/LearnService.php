<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\CourseAnswer;
use App\Models\CourseQuestion;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Models\User;

class LearnService
{
    public function __construct(private ProgressService $progress)
    {
    }

    public function submitQuiz(User $user, Course $course, Lesson $lesson, array $answers): array
    {
        abort_unless($lesson->course_id === $course->id && $lesson->quiz, 404);

        $quiz = $lesson->quiz()->with('questions')->first();

        abort_if(
            QuizAttempt::query()->where('quiz_id', $quiz->id)->where('user_id', $user->id)->exists(),
            403,
            __('You have already attempted this quiz.')
        );

        $correct = 0;

        foreach ($quiz->questions as $question) {
            if ((int) ($answers[$question->id] ?? -1) === (int) $question->correct_index) {
                $correct++;
            }
        }

        $total = max($quiz->questions->count(), 1);
        $score = (int) round(($correct / $total) * 100);
        $passed = $score >= $quiz->pass_percent;

        $attempt = QuizAttempt::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => $score,
            'passed' => $passed,
            'answers' => $answers,
        ]);

        ActivityLog::record('quiz.submitted', $attempt, ['title' => $lesson->title, 'score' => $score]);

        $courseJustCompleted = false;

        if ($passed) {
            $courseJustCompleted = $this->progress->markCompleted($user, $lesson)['justCompletedCourse'];
        }

        return [
            'passed' => $passed,
            'score' => $score,
            'course_just_completed' => $courseJustCompleted,
            'message' => $passed
                ? __('Quiz passed with :score%.', ['score' => $score])
                : __('Quiz failed with :score%.', ['score' => $score]),
        ];
    }

    public function ask(User $user, Course $course, Lesson $lesson, array $data): CourseQuestion
    {
        $question = CourseQuestion::query()->create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
            'title' => $data['title'],
            'body' => $data['body'],
        ]);

        ActivityLog::record('question.asked', $question, ['course' => $course->translation()?->title]);

        return $question;
    }

    public function answer(User $user, Course $course, CourseQuestion $question, array $data): CourseAnswer
    {
        abort_unless($question->course_id === $course->id, 404);

        $answer = CourseAnswer::query()->create([
            'course_question_id' => $question->id,
            'user_id' => $user->id,
            'body' => $data['body'],
        ]);

        ActivityLog::record('question.answered', $answer, ['course' => $course->translation()?->title]);

        return $answer;
    }
}
