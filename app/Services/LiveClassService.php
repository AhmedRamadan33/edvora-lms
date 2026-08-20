<?php

namespace App\Services;

use App\Models\Course;
use App\Models\LiveClass;
use App\Models\User;
use App\Notifications\LiveClassScheduledNotification;
use App\Repositories\LiveClassRepository;
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

        $this->notifyEnrolledStudents($liveClass);

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

        return $liveClass;
    }

    public function cancel(LiveClass $liveClass): void
    {
        $this->providerService($liveClass->provider)->deleteMeeting($liveClass->instructor, $liveClass);

        $liveClass->update([
            'status' => LiveClass::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
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

    protected function notifyEnrolledStudents(LiveClass $liveClass): void
    {
        $students = $liveClass->course->enrollments()->with('user')->get()->pluck('user')->filter();

        if ($students->isEmpty()) {
            return;
        }

        Notification::send($students, new LiveClassScheduledNotification($liveClass));
    }
}
