<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\AnswerQuestionRequest;
use App\Http\Requests\Student\AskQuestionRequest;
use App\Http\Requests\Student\SubmitQuizRequest;
use App\Models\Course;
use App\Models\CourseQuestion;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\BunnyStreamService;
use App\Services\LearnService;
use App\Services\ProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LearnController extends Controller
{
    public function show(Course $course): View
    {
        $this->ensureEnrolled($course);

        $course->load(['translations', 'sections.lessons']);
        $progress = LessonProgress::query()
            ->where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->get()
            ->keyBy('lesson_id');

        return view('learn.course', compact('course', 'progress'));
    }

    public function lesson(Course $course, Lesson $lesson, BunnyStreamService $bunny): View
    {
        $this->ensureEnrolled($course);
        abort_unless($lesson->course_id === $course->id, 404);

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

        return view('learn.lesson', [
            'course' => $course->load('translations'),
            'lesson' => $lesson,
            'embedUrl' => $embedUrl,
            'watermark' => auth()->user()->email.' #'.auth()->id(),
            'questions' => $questions,
        ]);
    }

    public function complete(Course $course, Lesson $lesson, ProgressService $progress): RedirectResponse
    {
        $this->ensureEnrolled($course);
        abort_unless($lesson->course_id === $course->id, 404);
        $progress->markCompleted(auth()->user(), $lesson);

        return back()->with('success', __('Lesson completed.'));
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
}
