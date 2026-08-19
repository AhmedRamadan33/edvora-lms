<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->published()
            ->with(['translations', 'instructor', 'category.translations'])
            ->when($request->category, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($request->q, function ($q, $term) {
                $q->whereHas('translations', fn ($t) => $t->where('title', 'like', "%{$term}%"));
            })
            ->when($request->level, fn ($q, $level) => $q->where('level', $level))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()->where('is_active', true)->with('translations')->orderBy('sort_order')->get();

        return view('catalog.index', compact('courses', 'categories'));
    }

    public function show(string $slug, ReviewRepository $reviews): View
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->published()
            ->with([
                'translations',
                'instructor.instructorProfile',
                'category.translations',
                'sections.lessons',
                'approvedReviews.user',
            ])
            ->firstOrFail();

        $enrolled = auth()->check() && auth()->user()->isEnrolledIn($course->id);
        $ownReview = auth()->check() ? $reviews->findOwnedByUser($course->id, auth()->id()) : null;

        $cover = $course->thumbnail
            ? asset('storage/'.$course->thumbnail)
            : 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1600&q=80';

        return view('catalog.show', compact('course', 'enrolled', 'cover', 'ownReview'));
    }
}
