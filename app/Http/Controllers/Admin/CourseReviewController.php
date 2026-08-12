<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectCourseRequest;
use App\Models\Category;
use App\Models\Course;
use App\Services\AdminCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseReviewController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->with(['translations', 'instructor', 'category.translations'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status), fn ($q) => $q->whereIn('status', ['pending_review', 'published', 'rejected']))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search')->trim()->toString();

                $query->where(function ($courseQuery) use ($term) {
                    $courseQuery
                        ->whereHas('translations', fn ($translationQuery) => $translationQuery->where('title', 'like', "%{$term}%"))
                        ->orWhereHas('instructor', fn ($instructorQuery) => $instructorQuery->where('name', 'like', "%{$term}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = Category::query()->where('is_active', true)->with('translations')->orderBy('sort_order')->get();

        return view('admin.courses.index', compact('courses', 'categories'));
    }

    public function show(Course $course): View
    {
        $course->load(['translations', 'instructor', 'sections.lessons.video']);

        return view('admin.courses.show', compact('course'));
    }

    public function approve(Course $course, AdminCatalogService $catalog): RedirectResponse
    {
        $catalog->approveCourse($course);

        return back()->with('success', __('Course published.'));
    }

    public function reject(RejectCourseRequest $request, Course $course, AdminCatalogService $catalog): RedirectResponse
    {
        $catalog->rejectCourse($course, $request->validated('rejection_reason'));

        return back()->with('success', __('Course rejected.'));
    }
}
