<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['course_id', 'locale', 'title', 'subtitle', 'description'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
