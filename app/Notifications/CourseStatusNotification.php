<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public Course $course, public string $status) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Course status updated'))
            ->line(__('Your course ":title" is now :status.', [
                'title' => $this->course->translation('en')?->title ?? 'Course',
                'status' => $this->status,
            ]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'course_id' => $this->course->id,
            'status' => $this->status,
        ];
    }
}
