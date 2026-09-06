<?php

namespace App\Services;

use App\Models\InstructorProfile;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Notification;

class InstructorApplicationService
{
    public function applyAccountType(User $user, string $accountType): void
    {
        $user->syncRoles([$accountType]);

        if ($accountType !== 'instructor') {
            return;
        }

        InstructorProfile::query()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $admins = User::role('admin')->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new GenericNotification(
                __(':name applied to become an instructor.', ['name' => $user->name]),
                route('admin.instructors.index'),
                __('New instructor application')
            ));
        }
    }
}
