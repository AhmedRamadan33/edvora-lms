<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\AnswerQuestionRequest;
use App\Http\Requests\Student\AskQuestionRequest;
use App\Http\Requests\Student\SubmitQuizRequest;
use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Services\BunnyStreamService;
use App\Services\LearnService;
use App\Services\ProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LearnController extends Controller
{
    public function show(Course $course): View
    {
        $this->ensureEnrolled($course);

        $course->load(['translations', 'sections.lessons']);
        $progress = $this->progressFor($course);
        $enrollment = $this->enrollmentFor($course);

        return view('learn.course', compact('course', 'progress', 'enrollment'));
    }

    public function lesson(Course $course, Lesson $lesson, BunnyStreamService $bunny): View
    {
        $this->ensureEnrolled($course);
        abort_unless($lesson->course_id === $course->id, 404);

        $course->load(['translations', 'sections.lessons']);
        $lesson->load(['video', 'quiz.questions']);
        $embedUrl = null;

        if ($lesson->type === 'video' && $lesson->video) {
            $embedUrl = $bunny->embedUrl($lesson->video);
        }

        $questions = CourseQuestion::query()
            ->where('course_id', $course->id)
            ->where(function ($q) use ($lesson) {
                $q->whereNull('lesson_id')->orWhere('lesson_id', $lesson->id);
            })
            ->with(['user', 'answers.user'])
            ->latest()
            ->get();

        [$previousLesson, $nextLesson] = $this->neighboringLessons($course, $lesson);

        $quizAttempt = $lesson->type === 'quiz' && $lesson->quiz
            ? QuizAttempt::query()
                ->where('quiz_id', $lesson->quiz->id)
                ->where('user_id', auth()->id())
                ->latest()
                ->first()
            : null;

        return view('learn.lesson', [
            'course' => $course,
            'lesson' => $lesson,
            'embedUrl' => $embedUrl,
            'watermark' => auth()->user()->email.' #'.auth()->id(),
            'questions' => $questions,
            'progress' => $this->progressFor($course),
            'enrollment' => $this->enrollmentFor($course),
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'quizAttempt' => $quizAttempt,
        ]);
    }

    public function complete(Course $course, Lesson $lesson, ProgressService $progress): RedirectResponse
    {
        $this->ensureEnrolled($course);
        abort_unless($lesson->course_id === $course->id, 404);
        $result = $progress->markCompleted(auth()->user(), $lesson);

        return back()->with('success', $result['justCompletedCourse']
            ? __('Course completed successfully! You can download your certificate from the certificates page.')
            : __('Lesson completed.'));
    }

    public function submitQuiz(SubmitQuizRequest $request, Course $course, Lesson $lesson, LearnService $learn): RedirectResponse
    {
        $this->ensureEnrolled($course);
        $result = $learn->submitQuiz(
            auth()->user(),
            $course,
            $lesson,
            $request->validated('answers') ?? []
        );

        if ($result['passed'] && $result['course_just_completed']) {
            return back()->with('success', __('Course completed successfully! You can download your certificate from the certificates page.'));
        }

        return back()->with($result['passed'] ? 'success' : 'error', $result['message']);
    }

    public function ask(AskQuestionRequest $request, Course $course, Lesson $lesson, LearnService $learn): RedirectResponse
    {
        $this->ensureEnrolled($course);
        $learn->ask(auth()->user(), $course, $lesson, $request->validated());

        return back()->with('success', __('Question posted.'));
    }

    public function answer(AnswerQuestionRequest $request, Course $course, CourseQuestion $question, LearnService $learn): RedirectResponse
    {
        $this->ensureEnrolled($course);
        $learn->answer(auth()->user(), $course, $question, $request->validated());

        return back()->with('success', __('Answer posted.'));
    }

    protected function ensureEnrolled(Course $course): void
    {
        abort_unless(auth()->user()->isEnrolledIn($course->id) || auth()->user()->hasRole('admin'), 403);
    }

    protected function progressFor(Course $course): Collection
    {
        return LessonProgress::query()
            ->where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->get()
            ->keyBy('lesson_id');
    }

    protected function enrollmentFor(Course $course): ?Enrollment
    {
        return Enrollment::query()
            ->where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->first();
    }

    /**
     * @return array{0: ?Lesson, 1: ?Lesson}
     */
    protected function neighboringLessons(Course $course, Lesson $lesson): array
    {
        $ordered = $course->sections->flatMap(fn ($section) => $section->lessons)->values();
        $index = $ordered->search(fn (Lesson $item) => $item->id === $lesson->id);

        if ($index === false) {
            return [null, null];
        }

        return [
            $index > 0 ? $ordered->get($index - 1) : null,
            $ordered->get($index + 1),
        ];
    }
}
