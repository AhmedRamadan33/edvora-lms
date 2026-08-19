<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instructor\RequestVideoCredentialsRequest;
use App\Http\Requests\Instructor\StoreLessonRequest;
use App\Http\Requests\Instructor\StoreSectionRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\Video;
use App\Services\CurriculumService;
use Illuminate\Http\JsonResponse;
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

    public function videoUploadCredentials(RequestVideoCredentialsRequest $request, Course $course, CurriculumService $curriculum): JsonResponse
    {
        return response()->json($curriculum->requestVideoUploadCredentials($request->validated('title')));
    }

    public function checkVideoStatus(Course $course, Lesson $lesson, CurriculumService $curriculum): RedirectResponse
    {
        $this->authorizeCourse($course);
        $video = $curriculum->checkVideoStatus($course, $lesson);

        $messages = [
            Video::STATUS_READY => __('Video is ready.'),
            Video::STATUS_PROCESSING => __('Video is still processing.'),
            Video::STATUS_PENDING => __('Video upload has not been confirmed yet.'),
            Video::STATUS_FAILED => __('Video processing failed.'),
        ];

        return back()->with('success', $messages[$video->status] ?? __('Video status updated.'));
    }

    protected function authorizeCourse(Course $course): void
    {
        abort_unless($course->instructor_id === auth()->id() || auth()->user()->hasRole('admin'), 403);
    }
}
