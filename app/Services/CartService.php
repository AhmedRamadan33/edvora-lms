<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\CartItem;
use App\Models\Course;
use App\Models\User;

class CartService
{
    public function add(User $user, Course $course): CartItem
    {
        $item = CartItem::query()->firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        ActivityLog::record('cart.item_added', $course, ['course' => $course->translation()?->title]);

        return $item;
    }

    public function remove(User $user, Course $course): void
    {
        CartItem::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->delete();

        ActivityLog::record('cart.item_removed', $course, ['course' => $course->translation()?->title]);
    }
}
