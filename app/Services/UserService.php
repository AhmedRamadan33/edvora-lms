<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;

class UserService
{
    public function toggleActive(User $user): User
    {
        $user->update(['is_active' => ! $user->is_active]);

        ActivityLog::record(
            $user->is_active ? 'user.activated' : 'user.deactivated',
            $user,
            ['name' => $user->name]
        );

        return $user;
    }
}
