<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveClass extends Model
{
    public const PROVIDER_ZOOM = 'zoom';

    public const PROVIDER_GOOGLE_MEET = 'google_meet';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_FAILED = 'failed';

    public const STATE_UPCOMING = 'upcoming';

    public const STATE_LIVE = 'live';

    public const STATE_ENDED = 'ended';

    protected $fillable = [
        'course_id', 'instructor_id', 'provider', 'title', 'description',
        'scheduled_at', 'duration_minutes', 'provider_meeting_id',
        'join_url', 'start_url', 'status', 'reminder_sent_at', 'cancelled_at', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'duration_minutes' => 'integer',
            'meta' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function endsAt(): \Illuminate\Support\Carbon
    {
        return $this->scheduled_at->clone()->addMinutes($this->duration_minutes);
    }

    public function computedState(): string
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return self::STATUS_CANCELLED;
        }

        if ($this->status === self::STATUS_FAILED) {
            return self::STATUS_FAILED;
        }

        $now = now();

        if ($now->lt($this->scheduled_at)) {
            return self::STATE_UPCOMING;
        }

        if ($now->lte($this->endsAt())) {
            return self::STATE_LIVE;
        }

        return self::STATE_ENDED;
    }

    public function isJoinable(): bool
    {
        if (in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_FAILED], true)) {
            return false;
        }

        $now = now();

        return $now->gte($this->scheduled_at->clone()->subMinutes(10)) && $now->lte($this->endsAt());
    }
}
