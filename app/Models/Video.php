<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    protected $fillable = [
        'lesson_id', 'bunny_video_id', 'library_id', 'status', 'title', 'length_seconds',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function isReady(): bool
    {
        return $this->status === 'ready' && filled($this->bunny_video_id);
    }
}
