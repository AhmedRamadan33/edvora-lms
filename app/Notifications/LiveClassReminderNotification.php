<?php

namespace App\Notifications;

use App\Models\LiveClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LiveClassReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LiveClass $liveClass) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your live class starts soon'))
            ->line(__('":title" is starting soon in :course.', [
                'title' => $this->liveClass->title,
                'course' => $this->liveClass->course->translation()?->title,
            ]))
            ->line(__('Scheduled at: :datetime', ['datetime' => $this->liveClass->scheduledAtLocal()->format('Y-m-d H:i')]))
            ->action(__('View course'), route('learn.course', $this->liveClass->course));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'live_class_id' => $this->liveClass->id,
            'course_id' => $this->liveClass->course_id,
            'title' => $this->liveClass->title,
            'message' => 'Live class starting soon',
            'url' => route('learn.course', $this->liveClass->course),
        ];
    }
}
