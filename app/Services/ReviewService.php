<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Review;
use App\Models\User;

class ReviewService
{
    public function save(User $user, Course $course, array $data): Review
    {
        abort_unless($user->isEnrolledIn($course->id), 403);

        $review = Review::query()->updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            $data
        );

        $course->update([
            'avg_rating' => round($course->reviews()->avg('rating'), 2),
            'reviews_count' => $course->reviews()->count(),
        ]);

        return $review;
    }
}
