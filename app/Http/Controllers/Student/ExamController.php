<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitExamAttemptRequest;
use App\Models\Exam;
use App\Services\StudentExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(StudentExamService $exams): View
    {
        return view('student.exams.index', [
            'exams' => $exams->availableForStudent(auth()->user()),
        ]);
    }

    public function show(Exam $exam, StudentExamService $exams): View|RedirectResponse
    {
        $exams->ensureAccessible($exam, auth()->user());

        $attempt = $exam->attempts()->where('user_id', auth()->id())->first();

        if ($attempt) {
            return redirect()->route(
                $attempt->status === 'in_progress' ? 'exams.attempt' : 'exams.result',
                $exam
            );
        }

        return view('student.exams.show', [
            'exam' => $exam->load('course.translations'),
            'questionCount' => $exam->examQuestions()->count(),
        ]);
    }

    public function start(Exam $exam, StudentExamService $exams): RedirectResponse
    {
        $exams->startAttempt($exam, auth()->user());

        return redirect()->route('exams.attempt', $exam);
    }

    public function attempt(Exam $exam, StudentExamService $exams): View|RedirectResponse
    {
        $exams->ensureAccessible($exam, auth()->user());

        $attempt = $exam->attempts()->where('user_id', auth()->id())->first();
        abort_unless($attempt, 404);

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('exams.result', $exam);
        }

        return view('student.exams.attempt', [
            'exam' => $exam->load('course.translations'),
            'attempt' => $attempt,
            'questions' => $exam->questions()->with('choices', 'matches', 'subject')->get(),
        ]);
    }

    public function submit(SubmitExamAttemptRequest $request, Exam $exam, StudentExamService $exams): RedirectResponse
    {
        $attempt = $exam->attempts()->where('user_id', auth()->id())->first();
        abort_unless($attempt, 404);

        $exams->submitAttempt($attempt, $request->validated('answers') ?? []);

        return redirect()->route('exams.result', $exam)->with('success', __('Exam submitted.'));
    }

    public function result(Exam $exam, StudentExamService $exams): View
    {
        $exams->ensureAccessible($exam, auth()->user());

        $attempt = $exam->attempts()->where('user_id', auth()->id())->first();
        abort_unless($attempt, 404);
        abort_if($attempt->status === 'in_progress', 404);

        return view('student.exams.result', [
            'exam' => $exam->load('course.translations'),
            ...$exams->resultFor($attempt),
        ]);
    }
}
