<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreBankQuestionRequest;
use App\Http\Requests\Instructor\UpdateBankQuestionRequest;
use App\Models\BankQuestion;
use App\Models\Course;
use App\Repositories\SubjectRepository;
use App\Services\BankQuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankQuestionController extends Controller
{
    public function index(Request $request, Course $course, BankQuestionService $bank, SubjectRepository $subjects): View
    {
        $this->authorizeCourse($course);

        $data = $bank->listForCourse($course, [
            'type' => $request->get('type'),
            'difficulty' => $request->get('difficulty'),
            'subject_id' => $request->get('subject_id'),
            'search' => $request->get('search'),
            'is_active' => $request->filled('is_active') ? $request->boolean('is_active') : null,
        ]);

        return view('instructor.courses.question-bank.index', $data + [
            'course' => $course,
            'subjects' => $subjects->forCourse($course->id),
        ]);
    }

    public function store(StoreBankQuestionRequest $request, Course $course, BankQuestionService $bank): RedirectResponse
    {
        $this->authorizeCourse($course);

        $bank->create($course, $request->user(), $request->validated(), $request->file('image'));

        return back()->with('success', __('Question added.'));
    }

    public function update(UpdateBankQuestionRequest $request, Course $course, BankQuestion $bankQuestion, BankQuestionService $bank): RedirectResponse
    {
        $this->authorizeCourse($course, $bankQuestion);

        $bank->update($bankQuestion, $request->validated(), $request->file('image'));

        return back()->with('success', __('Question updated.'));
    }

    public function destroy(Course $course, BankQuestion $bankQuestion, BankQuestionService $bank): RedirectResponse
    {
        $this->authorizeCourse($course, $bankQuestion);

        $bank->delete($bankQuestion);

        return back()->with('success', __('Question deleted.'));
    }

    public function toggleActive(Course $course, BankQuestion $bankQuestion, BankQuestionService $bank): RedirectResponse
    {
        $this->authorizeCourse($course, $bankQuestion);

        $bank->toggleActive($bankQuestion);

        return back()->with('success', __('Question status updated.'));
    }

    protected function authorizeCourse(Course $course, ?BankQuestion $bankQuestion = null): void
    {
        abort_unless($course->instructor_id === auth()->id() || auth()->user()->hasRole('admin'), 403);
        abort_if($bankQuestion && $bankQuestion->course_id !== $course->id, 404);
    }
}
