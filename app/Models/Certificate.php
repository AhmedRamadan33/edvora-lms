<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    protected $fillable = ['uuid', 'user_id', 'course_id', 'code', 'issued_at'];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (Certificate $certificate) {
            $certificate->uuid ??= (string) Str::uuid();
            $certificate->code ??= strtoupper(Str::random(10));
            $certificate->issued_at ??= now();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
