<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\StoreLessonRequest;
use App\Http\Requests\Instructor\StoreSectionRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Services\CurriculumService;
use Illuminate\Http\RedirectResponse;

class CurriculumController extends Controller
{
    public function storeSection(StoreSectionRequest $request, Course $course, CurriculumService $curriculum): RedirectResponse
    {
        $this->authorizeCourse($course);
        $curriculum->addSection($course, $request->validated('title'));

        return back()->with('success', __('Section added.'));
    }

    public function storeLesson(StoreLessonRequest $request, Course $course, Section $section, CurriculumService $curriculum): RedirectResponse
    {
        $this->authorizeCourse($course);

        $data = $request->validated();
        $data['is_preview'] = $request->boolean('is_preview');

        $curriculum->addLesson($course, $section, $data, $request->file('attachment'));

        return back()->with('success', __('Lesson added.'));
    }

    public function destroyLesson(Course $course, Lesson $lesson, CurriculumService $curriculum): RedirectResponse
    {
        $this->authorizeCourse($course);
        $curriculum->deleteLesson($course, $lesson);

        return back()->with('success', __('Lesson deleted.'));
    }

    public function markVideoReady(Course $course, Lesson $lesson, CurriculumService $curriculum): RedirectResponse
    {
        $this->authorizeCourse($course);
        $curriculum->markVideoReady($course, $lesson);

        return back()->with('success', __('Video marked ready.'));
    }

    protected function authorizeCourse(Course $course): void
    {
        abort_unless($course->instructor_id === auth()->id(), 403);
    }
}
