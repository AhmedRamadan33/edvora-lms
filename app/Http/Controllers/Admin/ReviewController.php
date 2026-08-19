<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterReviewsRequest;
use App\Http\Requests\Admin\RejectReviewRequest;
use App\Models\Course;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(FilterReviewsRequest $request, ReviewService $reviews): View
    {
        return view('admin.reviews.index', [
            'reviews' => $reviews->listForAdmin($request->validated()),
            'courses' => Course::query()->with('translations')->orderBy('id')->get(),
        ]);
    }

    public function approve(Review $review, ReviewService $reviews): RedirectResponse
    {
        $reviews->approve($review, auth()->user());

        return back()->with('success', __('Review approved.'));
    }

    public function reject(RejectReviewRequest $request, Review $review, ReviewService $reviews): RedirectResponse
    {
        $reviews->reject($review, auth()->user(), $request->validated('admin_note'));

        return back()->with('success', __('Review rejected.'));
    }

    public function destroy(Review $review, ReviewService $reviews): RedirectResponse
    {
        $reviews->deleteAsAdmin($review);

        return back()->with('success', __('Review deleted.'));
    }
}
