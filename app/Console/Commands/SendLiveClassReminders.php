<?php

namespace App\Console\Commands;

use App\Notifications\LiveClassReminderNotification;
use App\Repositories\LiveClassRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendLiveClassReminders extends Command
{
    protected $signature = 'live-classes:send-reminders';

    protected $description = 'Send a reminder notification to enrolled students for live classes starting in about 15 minutes';

    public function handle(LiveClassRepository $liveClasses): int
    {
        $dueClasses = $liveClasses->dueForReminder();

        foreach ($dueClasses as $liveClass) {
            $students = $liveClass->course->enrollments()->with('user')->get()->pluck('user')->filter();

            if ($students->isNotEmpty()) {
                Notification::send($students, new LiveClassReminderNotification($liveClass));
            }

            $liveClass->update(['reminder_sent_at' => now()]);
        }

        $this->info("Reminders sent for {$dueClasses->count()} live class(es).");

        return self::SUCCESS;
    }
}
