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
            : asset('images/course_thumbnail.png');

        $translation = $course->translation();
        $courseSchemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $translation?->title,
            'description' => \Illuminate\Support\Str::limit(strip_tags($translation?->description ?: $translation?->subtitle ?: ''), 300),
            'provider' => [
                '@type' => 'Organization',
                'name' => \App\Services\SettingService::platformName(),
                'sameAs' => url('/'),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return view('catalog.show', compact('course', 'enrolled', 'cover', 'ownReview', 'courseSchemaJson'));
    }
}
