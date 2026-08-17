<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    protected $fillable = ['exam_id', 'bank_question_id', 'sort_order'];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function bankQuestion(): BelongsTo
    {
        return $this->belongsTo(BankQuestion::class);
    }
}
