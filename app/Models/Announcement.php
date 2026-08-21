<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'sender_id', 'subject', 'body', 'audience', 'recipient_ids', 'recipients_count',
    ];

    protected function casts(): array
    {
        return [
            'recipient_ids' => 'array',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
