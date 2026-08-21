<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository extends BaseRepository
{
    public function __construct(DatabaseNotification $model)
    {
        parent::__construct($model);
    }

    public function recentForUser(User $user, int $limit = 8): Collection
    {
        return $user->notifications()->limit($limit)->get();
    }

    public function paginateForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->notifications()->paginate($perPage);
    }

    public function unreadCountForUser(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAsRead(DatabaseNotification $notification): void
    {
        $notification->markAsRead();
    }

    public function markAllAsReadForUser(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}
