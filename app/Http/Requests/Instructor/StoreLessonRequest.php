<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:180'],
            'type' => ['required', 'in:video,article,file,quiz'],
            'content' => ['nullable', 'string', 'required_if:type,article'],
            'is_preview' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'max:10240', 'required_if:type,file'],
            'quiz_title' => ['nullable', 'string', 'max:180'],
            'pass_percent' => ['nullable', 'integer', 'min:1', 'max:100'],
            'questions' => ['nullable', 'array', 'required_if:type,quiz', 'min:1'],
            'questions.*.question' => ['required_if:type,quiz', 'string'],
            'questions.*.options' => ['required_if:type,quiz', 'array', 'min:2'],
            'questions.*.correct_index' => ['required_if:type,quiz', 'integer', 'min:0', 'max:3'],
        ];
    }
}
