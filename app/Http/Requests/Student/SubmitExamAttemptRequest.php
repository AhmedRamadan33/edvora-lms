<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitExamAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $exam = $this->route('exam');
        $user = $this->user();

        if (! $user || ! $exam) {
            return false;
        }

        return $user->isEnrolledIn($exam->course_id) || $user->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'answers' => ['nullable', 'array'],
        ];
    }
}
