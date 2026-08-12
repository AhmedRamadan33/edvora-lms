<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreReviewRequest;
use App\Models\Course;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Course $course, ReviewService $reviews): RedirectResponse
    {
        $reviews->save(auth()->user(), $course, $request->validated());

        return back()->with('success', __('Review saved.'));
    }
}
