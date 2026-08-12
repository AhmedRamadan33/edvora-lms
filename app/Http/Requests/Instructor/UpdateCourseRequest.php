<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        return $this->user()
            && $course
            && ($course->instructor_id === $this->user()->id || $this->user()->hasRole('admin'));
    }

    public function rules(): array
    {
        return [
            'title_en' => ['required', 'string', 'max:180'],
            'title_ar' => ['required', 'string', 'max:180'],
            'subtitle_en' => ['nullable', 'string', 'max:255'],
            'subtitle_ar' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'level' => ['required', 'in:beginner,intermediate,advanced'],
            'language' => ['required', 'in:en,ar'],
            'price' => ['required', 'numeric', 'min:0'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
