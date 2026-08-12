<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $courses = Course::query()
            ->published()
            ->with(['translations', 'instructor', 'category.translations'])
            ->latest('published_at')
            ->take(8)
            ->get();

        $featured = Course::query()
            ->published()
            ->where('is_featured', true)
            ->with(['translations', 'instructor'])
            ->take(4)
            ->get();

        $categories = Category::query()
            ->where('is_active', true)
            ->with('translations')
            ->orderBy('sort_order')
            ->get();

        $testimonials = Testimonial::query()
            ->published()
            ->where('show_on_home', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        return view('home', compact('courses', 'featured', 'categories', 'testimonials'));
    }
}
