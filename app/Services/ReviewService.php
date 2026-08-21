<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use App\Repositories\ReviewRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService
{
    public function __construct(private ReviewRepository $reviews)
    {
    }

    public function save(User $user, Course $course, array $data): Review
    {
        abort_unless($user->isEnrolledIn($course->id), 403);

        $review = $this->reviews->updateOrCreateForUserAndCourse($user->id, $course->id, [
            ...$data,
            'status' => Review::STATUS_APPROVED,
        ]);

        $this->recalculateCourseRating($course);

        ActivityLog::record('review.saved', $review, ['course' => $course->translation()?->title, 'rating' => $review->rating]);

        return $review;
    }

    public function deleteOwn(User $user, Review $review): void
    {
        abort_unless($review->user_id === $user->id, 403);

        $course = $review->course;
        $this->reviews->delete($review);
        $this->recalculateCourseRating($course);

        ActivityLog::record('review.deleted', $review, ['course' => $course->translation()?->title]);
    }

    public function approve(Review $review, User $admin): void
    {
        $review->update([
            'status' => Review::STATUS_APPROVED,
            'moderated_by' => $admin->id,
            'moderated_at' => now(),
        ]);

        $this->recalculateCourseRating($review->course);

        ActivityLog::record('review.approved', $review, ['course' => $review->course?->translation()?->title]);
    }

    public function reject(Review $review, User $admin, string $note): void
    {
        $review->update([
            'status' => Review::STATUS_REJECTED,
            'admin_note' => $note,
            'moderated_by' => $admin->id,
            'moderated_at' => now(),
        ]);

        $this->recalculateCourseRating($review->course);

        ActivityLog::record('review.rejected', $review, ['course' => $review->course?->translation()?->title]);
    }

    public function deleteAsAdmin(Review $review): void
    {
        $course = $review->course;
        $this->reviews->delete($review);
        $this->recalculateCourseRating($course);

        ActivityLog::record('review.deleted_by_admin', $review, ['course' => $course->translation()?->title]);
    }

    public function listForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->reviews->paginateForAdmin($filters);
    }

    protected function recalculateCourseRating(Course $course): void
    {
        $course->update([
            'avg_rating' => round((float) $course->approvedReviews()->avg('rating'), 2),
            'reviews_count' => $course->approvedReviews()->count(),
        ]);
    }
}
