<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use App\Notifications\GenericNotification;
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

        if ($course->instructor_id !== $user->id) {
            $course->instructor?->notify(new GenericNotification(
                __(':name left a :rating-star review on ":course".', [
                    'name' => $user->name,
                    'rating' => $review->rating,
                    'course' => $course->translation()?->title,
                ]),
                route('instructor.courses.edit', $course),
                __('New course review')
            ));
        }

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

        $review->user?->notify(new GenericNotification(
            __('Your review on ":course" was approved.', ['course' => $review->course?->translation()?->title]),
            route('learn.course', $review->course),
            __('Review approved')
        ));
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

        $review->user?->notify(new GenericNotification(
            __('Your review on ":course" was rejected. Reason: :reason', [
                'course' => $review->course?->translation()?->title,
                'reason' => $note,
            ]),
            route('learn.course', $review->course),
            __('Review rejected')
        ));
    }

    public function deleteAsAdmin(Review $review): void
    {
        $course = $review->course;
        $author = $review->user;
        $this->reviews->delete($review);
        $this->recalculateCourseRating($course);

        ActivityLog::record('review.deleted_by_admin', $review, ['course' => $course->translation()?->title]);

        $author?->notify(new GenericNotification(
            __('Your review on ":course" was removed by an administrator.', ['course' => $course->translation()?->title]),
            route('learn.course', $course),
            __('Review removed')
        ));
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
