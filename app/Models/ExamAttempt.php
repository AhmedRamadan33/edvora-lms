<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id', 'user_id', 'started_at', 'submitted_at', 'status', 'total_points', 'auto_score', 'passed',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'total_points' => 'integer',
            'auto_score' => 'integer',
            'passed' => 'boolean',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class);
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'submitted';
    }

    public function scorePercent(): int
    {
        return $this->total_points > 0 ? (int) round(($this->auto_score / $this->total_points) * 100) : 0;
    }
}
