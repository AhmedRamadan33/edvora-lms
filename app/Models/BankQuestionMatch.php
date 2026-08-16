<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankQuestionMatch extends Model
{
    protected $fillable = ['bank_question_id', 'prompt_text', 'match_text', 'sort_order'];

    public function bankQuestion(): BelongsTo
    {
        return $this->belongsTo(BankQuestion::class);
    }
}
