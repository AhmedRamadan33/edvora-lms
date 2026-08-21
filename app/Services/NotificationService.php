<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\NotificationRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationService
{
    public function __construct(private NotificationRepository $notifications)
    {
    }

    public function recentFor(User $user, int $limit = 8): Collection
    {
        return $this->notifications->recentForUser($user, $limit);
    }

    public function unreadCountFor(User $user): int
    {
        return $this->notifications->unreadCountForUser($user);
    }

    public function paginateFor(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->notifications->paginateForUser($user, $perPage);
    }

    public function openAndMarkRead(DatabaseNotification $notification): string
    {
        $this->notifications->markAsRead($notification);

        return $notification->data['url'] ?? route('notifications.index');
    }

    public function markAllReadFor(User $user): void
    {
        $this->notifications->markAllAsReadForUser($user);
    }
}
