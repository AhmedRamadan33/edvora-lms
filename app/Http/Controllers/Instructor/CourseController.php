<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreCourseRequest;
use App\Http\Requests\Instructor\UpdateCourseRequest;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request, CourseService $courses): View
    {
        $courses = $courses->paginateForInstructor(auth()->user(), search: $request->string('search')->trim()->toString() ?: null);

        return view('instructor.courses.index', compact('courses'));
    }

    public function create(CourseService $courses): View
    {
        $categories = $courses->activeCategories();

        return view('instructor.courses.create', compact('categories'));
    }

    public function store(StoreCourseRequest $request, CourseService $courses): RedirectResponse
    {
        $course = $courses->create(auth()->user(), $request->validated(), $request->file('thumbnail'));

        return redirect()->route('instructor.courses.edit', $course)->with('success', __('Course created.'));
    }

    public function edit(Course $course, CourseService $courses): View
    {
        $this->authorizeCourse($course);
        $course = $courses->loadForEdit($course);
        $categories = $courses->activeCategories();

        return view('instructor.courses.edit', compact('course', 'categories'));
    }

    public function update(UpdateCourseRequest $request, Course $course, CourseService $courses): RedirectResponse
    {
        $this->authorizeCourse($course);
        $courses->update($course, $request->validated(), $request->file('thumbnail'));

        return back()->with('success', __('Course updated.'));
    }

    public function submit(Course $course, CourseService $courses): RedirectResponse
    {
        $this->authorizeCourse($course);
        $result = $courses->submitForReview($course);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    protected function authorizeCourse(Course $course): void
    {
        abort_unless($course->instructor_id === auth()->id(), 403);
    }
}
