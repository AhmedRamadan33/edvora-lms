<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\AddExamQuestionsRequest;
use App\Http\Requests\Instructor\FilterInstructorExamsRequest;
use App\Http\Requests\Instructor\StoreExamRequest;
use App\Http\Requests\Instructor\UpdateExamRequest;
use App\Models\BankQuestion;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Services\ExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(FilterInstructorExamsRequest $request, ExamService $exams): View
    {
        return view('instructor.exams.index', [
            'exams' => $exams->listForInstructor($request->user(), $request->validated()),
            'courses' => $request->user()->courses()->with('translations')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $courses = $request->user()->courses()->with('translations')->get();
        $selectedCourseId = $request->get('course_id');
        $selectedCourse = $selectedCourseId ? $courses->firstWhere('id', (int) $selectedCourseId) : null;

        return view('instructor.exams.create', [
            'courses' => $courses,
            'selectedCourse' => $selectedCourse,
            'subjects' => $selectedCourse?->subjects()->orderBy('name')->get() ?? collect(),
        ]);
    }

    public function store(StoreExamRequest $request, ExamService $exams): RedirectResponse
    {
        $course = Course::query()->findOrFail($request->validated('course_id'));

        $result = $exams->create($course, $request->user(), $request->validated());

        return $this->withShortfallWarning(
            redirect()->route('instructor.exams.show', $result['exam'])->with('success', __('Exam created.')),
            $result['shortfalls'],
        );
    }

    public function show(Request $request, Exam $exam, ExamService $exams): View
    {
        $this->authorizeExam($exam);

        return view('instructor.exams.show', [
            'exam' => $exam->load('course.translations', 'course.subjects'),
            'examQuestions' => $exams->paginateQuestions($exam),
        ]);
    }

    public function edit(Exam $exam): View
    {
        $this->authorizeExam($exam);

        return view('instructor.exams.edit', ['exam' => $exam]);
    }

    public function update(UpdateExamRequest $request, Exam $exam, ExamService $exams): RedirectResponse
    {
        $exams->update($exam, $request->validated());

        return redirect()->route('instructor.exams.show', $exam)->with('success', __('Exam updated.'));
    }

    public function addQuestions(AddExamQuestionsRequest $request, Exam $exam, ExamService $exams): RedirectResponse
    {
        $shortfalls = $exams->addQuestions($exam, $request->validated('rules'));

        return $this->withShortfallWarning(
            redirect()->route('instructor.exams.show', $exam)->with('success', __('Questions added.')),
            $shortfalls,
        );
    }

    public function removeQuestion(Exam $exam, ExamQuestion $examQuestion, ExamService $exams): RedirectResponse
    {
        $this->authorizeExam($exam);

        $exams->removeQuestion($exam, $examQuestion);

        return back()->with('success', __('Question removed from exam.'));
    }

    public function destroy(Exam $exam, ExamService $exams): RedirectResponse
    {
        $this->authorizeExam($exam);

        $exams->delete($exam);

        return redirect()->route('instructor.exams.index')->with('success', __('Exam deleted.'));
    }

    public function toggleStatus(Exam $exam, ExamService $exams): RedirectResponse
    {
        $this->authorizeExam($exam);

        $exams->toggleStatus($exam);

        return back()->with('success', __('Exam status updated.'));
    }

    protected function authorizeExam(Exam $exam): void
    {
        abort_unless($exam->course->instructor_id === auth()->id() || auth()->user()->hasRole('admin'), 403);
    }

    protected function withShortfallWarning(RedirectResponse $redirect, array $shortfalls): RedirectResponse
    {
        if ($shortfalls === []) {
            return $redirect;
        }

        $lines = collect($shortfalls)->map(fn ($shortfall) => __('Only :added of the :requested requested ":type" questions were available for ":subject" - all :added were added.', [
            'added' => $shortfall['added'],
            'requested' => $shortfall['requested'],
            'type' => BankQuestion::typeLabel($shortfall['type']),
            'subject' => $shortfall['subject'],
        ]));

        return $redirect->with('warning', $lines->implode(' '));
    }
}
