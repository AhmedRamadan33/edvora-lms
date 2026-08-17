<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        $exam = $this->route('exam');

        return $this->user()
            && $exam
            && ($exam->course->instructor_id === $this->user()->id || $this->user()->hasRole('admin'));
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'pass_percent' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
