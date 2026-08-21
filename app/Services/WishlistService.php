<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\User;
use App\Models\Wishlist;

class WishlistService
{
    public function add(User $user, Course $course): Wishlist
    {
        $item = Wishlist::query()->firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        ActivityLog::record('wishlist.item_added', $course, ['course' => $course->translation()?->title]);

        return $item;
    }

    public function remove(User $user, Course $course): void
    {
        Wishlist::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->delete();

        ActivityLog::record('wishlist.item_removed', $course, ['course' => $course->translation()?->title]);
    }
}
