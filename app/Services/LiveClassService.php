<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\LiveClass;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Notifications\LiveClassScheduledNotification;
use App\Repositories\LiveClassRepository;
use Illuminate\Notifications\Notification as NotificationClass;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

class LiveClassService
{
    public function __construct(
        private LiveClassRepository $liveClasses,
        private ZoomService $zoom,
        private GoogleMeetService $googleMeet,
    ) {
    }

    public function schedule(Course $course, User $instructor, array $data): LiveClass
    {
        $provider = $data['provider'];
        $providerService = $this->providerService($provider);

        if (! $providerService->accessTokenFor($instructor)) {
            throw new RuntimeException("Instructor has not connected their {$this->providerLabel($provider)} account.");
        }

        $liveClass = new LiveClass([
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'provider' => $provider,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'],
            'status' => LiveClass::STATUS_SCHEDULED,
        ]);

        try {
            $meeting = $providerService->createMeeting($instructor, $liveClass);
        } catch (\Throwable $e) {
            $liveClass->status = LiveClass::STATUS_FAILED;
            $liveClass->meta = ['error' => $e->getMessage()];
            $liveClass->save();

            throw new RuntimeException('Unable to create the meeting: '.$e->getMessage(), previous: $e);
        }

        $liveClass->fill([
            'provider_meeting_id' => $meeting['meeting_id'],
            'join_url' => $meeting['join_url'],
            'start_url' => $meeting['start_url'],
            'meta' => $meeting['meta'],
        ]);
        $liveClass->save();

        $this->notifyEnrolledStudents($liveClass, new LiveClassScheduledNotification($liveClass));

        ActivityLog::record('live_class.scheduled', $liveClass, [
            'title' => $liveClass->title,
            'course' => $course->translation()?->title,
            'provider' => $this->providerLabel($provider),
        ]);

        return $liveClass;
    }

    public function reschedule(LiveClass $liveClass, array $data): LiveClass
    {
        $liveClass->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'],
        ]);

        $this->providerService($liveClass->provider)->updateMeeting($liveClass->instructor, $liveClass);

        $liveClass->save();

        ActivityLog::record('live_class.rescheduled', $liveClass, ['title' => $liveClass->title]);

        $this->notifyEnrolledStudents($liveClass, new GenericNotification(
            __('The live class ":title" was rescheduled to :datetime.', [
                'title' => $liveClass->title,
                'datetime' => $liveClass->scheduledAtLocal()->format('Y-m-d H:i'),
            ]),
            route('learn.course', $liveClass->course),
            __('Live class rescheduled')
        ));

        return $liveClass;
    }

    public function delete(LiveClass $liveClass): void
    {
        $this->providerService($liveClass->provider)->deleteMeeting($liveClass->instructor, $liveClass);

        $title = $liveClass->title;
        $course = $liveClass->course;

        $this->notifyEnrolledStudents($liveClass, new GenericNotification(
            __('The live class ":title" was cancelled.', ['title' => $title]),
            route('learn.course', $course),
            __('Live class cancelled')
        ));

        $liveClass->delete();

        ActivityLog::record('live_class.deleted', $liveClass, ['title' => $title]);
    }

    protected function providerService(string $provider): ZoomService|GoogleMeetService
    {
        return match ($provider) {
            LiveClass::PROVIDER_ZOOM => $this->zoom,
            LiveClass::PROVIDER_GOOGLE_MEET => $this->googleMeet,
            default => throw new RuntimeException("Unsupported live class provider: {$provider}"),
        };
    }

    protected function providerLabel(string $provider): string
    {
        return match ($provider) {
            LiveClass::PROVIDER_ZOOM => 'Zoom',
            LiveClass::PROVIDER_GOOGLE_MEET => 'Google Meet',
            default => $provider,
        };
    }

    protected function notifyEnrolledStudents(LiveClass $liveClass, NotificationClass $notification): void
    {
        $students = $liveClass->course->enrollments()->with('user')->get()->pluck('user')->filter();

        if ($students->isEmpty()) {
            return;
        }

        Notification::send($students, $notification);
    }
}
