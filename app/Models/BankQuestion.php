<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankQuestion extends Model
{
    public const TYPES = [
        'mcq_single',
        'true_false',
        'matching',
        'fill_blank',
        'essay',
    ];

    public const MANUALLY_GRADED_TYPES = ['essay', 'fill_blank'];

    protected $fillable = [
        'course_id', 'subject_id', 'created_by', 'type', 'question',
        'image', 'difficulty', 'points', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'points' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function choices(): HasMany
    {
        return $this->hasMany(BankQuestionChoice::class)->orderBy('sort_order');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(BankQuestionMatch::class)->orderBy('sort_order');
    }

    public function isAutoGraded(): bool
    {
        return ! in_array($this->type, self::MANUALLY_GRADED_TYPES, true);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'mcq_single' => __('Multiple choice'),
            'true_false' => __('True / False'),
            'matching' => __('Matching'),
            'fill_blank' => __('Fill in the blank'),
            'essay' => __('Essay'),
            default => $type,
        };
    }
}
