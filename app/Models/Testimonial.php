<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name', 'role', 'avatar', 'rating', 'content_en', 'content_ar',
        'is_published', 'show_on_home', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_on_home' => 'boolean',
        ];
    }

    public function content(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $locale === 'ar' ? $this->content_ar : $this->content_en;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
