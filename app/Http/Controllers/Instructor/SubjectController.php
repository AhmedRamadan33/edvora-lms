<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\FilterInstructorSubjectsRequest;
use App\Http\Requests\Instructor\StoreSubjectRequest;
use App\Models\Course;
use App\Models\Subject;
use App\Services\SubjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(FilterInstructorSubjectsRequest $request, SubjectService $subjects): View
    {
        return view('instructor.subjects.index', [
            'subjects' => $subjects->listForInstructor($request->user(), $request->validated()),
            'courses' => $request->user()->courses()->with('translations')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('instructor.subjects.create', [
            'courses' => $request->user()->courses()->with('translations')->get(),
            'selectedCourseId' => $request->get('course_id'),
        ]);
    }

    public function store(StoreSubjectRequest $request, SubjectService $subjects): RedirectResponse
    {
        $course = Course::query()->findOrFail($request->validated('course_id'));

        $subjects->create($course, $request->validated('name'));

        return redirect()->route('instructor.subjects.index')->with('success', __('Subject created.'));
    }

    public function destroy(Subject $subject, SubjectService $subjects): RedirectResponse
    {
        abort_unless($subject->course->instructor_id === auth()->id() || auth()->user()->hasRole('admin'), 403);

        $subjects->delete($subject);

        return back()->with('success', __('Subject deleted.'));
    }
}
