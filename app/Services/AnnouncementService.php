<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Repositories\AnnouncementRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class AnnouncementService
{
    public function __construct(
        private AnnouncementRepository $announcements,
        private UserRepository $users,
    ) {
    }

    public function paginateForSender(User $sender, int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->announcements->paginateForSender($sender->id, $perPage, $search);
    }

    public function studentsForAdmin(): Collection
    {
        return $this->users->allStudents();
    }

    public function studentsForInstructor(User $instructor): Collection
    {
        return $this->users->studentsForInstructor($instructor->id);
    }

    public function sendAsAdmin(User $admin, array $data): Announcement
    {
        $pool = $this->users->allStudents();
        $recipients = $data['audience'] === 'all'
            ? $pool
            : $pool->whereIn('id', $data['student_ids'] ?? []);

        return $this->send($admin, $data, $recipients);
    }

    public function sendAsInstructor(User $instructor, array $data): Announcement
    {
        $pool = $this->users->studentsForInstructor($instructor->id);
        $recipients = $data['audience'] === 'all'
            ? $pool
            : $pool->whereIn('id', $data['student_ids'] ?? []);

        return $this->send($instructor, $data, $recipients);
    }

    protected function send(User $sender, array $data, Collection $recipients): Announcement
    {
        $announcement = $this->announcements->create([
            'sender_id' => $sender->id,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'audience' => $data['audience'],
            'recipient_ids' => $recipients->pluck('id')->values()->all(),
            'recipients_count' => $recipients->count(),
        ]);

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new GenericNotification($data['body'], route('notifications.index'), $data['subject']));
        }

        ActivityLog::record('announcement.sent', $announcement, [
            'subject' => $announcement->subject,
            'count' => $announcement->recipients_count,
        ]);

        return $announcement;
    }
}
