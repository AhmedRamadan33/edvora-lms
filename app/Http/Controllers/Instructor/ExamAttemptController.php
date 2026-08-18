<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\FilterExamAttemptsRequest;
use App\Http\Requests\Instructor\GradeExamAttemptRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExamAttemptController extends Controller
{
    public function index(FilterExamAttemptsRequest $request, Exam $exam, ExamGradingService $grading): View
    {
        $this->authorizeExam($exam);

        return view('instructor.exams.attempts.index', [
            'exam' => $exam->load('course.translations'),
            'attempts' => $grading->listForExam($exam, $request->validated()),
        ]);
    }

    public function show(Exam $exam, ExamAttempt $attempt, ExamGradingService $grading): View
    {
        $this->authorizeExam($exam);

        return view('instructor.exams.attempts.show', [
            'exam' => $exam->load('course.translations'),
            'attempt' => $grading->findForGrading($exam, $attempt->id),
        ]);
    }

    public function grade(GradeExamAttemptRequest $request, Exam $exam, ExamAttempt $attempt, ExamGradingService $grading): RedirectResponse
    {
        $grading->grade($attempt, $request->user(), $request->validated('answers'));

        return redirect()->route('instructor.exams.attempts.show', [$exam, $attempt])->with('success', __('Grading saved.'));
    }

    protected function authorizeExam(Exam $exam): void
    {
        abort_unless($exam->course->instructor_id === auth()->id() || auth()->user()->hasRole('admin'), 403);
    }
}
