<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Wishlist;
use App\Services\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $items = Wishlist::query()
            ->where('user_id', auth()->id())
            ->with('course.translations')
            ->latest()
            ->get();

        return view('student.wishlist', compact('items'));
    }

    public function store(Course $course, WishlistService $wishlist): RedirectResponse
    {
        $wishlist->add(auth()->user(), $course);

        return back()->with('success', __('Added to wishlist.'));
    }

    public function destroy(Course $course, WishlistService $wishlist): RedirectResponse
    {
        $wishlist->remove(auth()->user(), $course);

        return back()->with('success', __('Removed from wishlist.'));
    }
}
