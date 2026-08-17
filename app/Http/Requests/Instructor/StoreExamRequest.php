<?php

namespace App\Http\Requests\Instructor;

use App\Models\BankQuestion;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['instructor', 'admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'pass_percent' => ['required', 'integer', 'min:1', 'max:100'],

            'rules' => ['required', 'array', 'min:1'],
            'rules.*.subject_id' => ['required', 'integer'],
            'rules.*.type' => ['required', Rule::in(BankQuestion::TYPES)],
            'rules.*.count' => ['required', 'integer', 'min:1', 'max:200'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $course = Course::query()->find($this->input('course_id'));

            if (! $course || ! ($course->instructor_id === $this->user()->id || $this->user()->hasRole('admin'))) {
                $validator->errors()->add('course_id', __('Invalid course.'));

                return;
            }

            $rules = collect($this->input('rules', []))
                ->filter(fn ($rule) => filled($rule['subject_id'] ?? null) && filled($rule['type'] ?? null));

            foreach ($rules as $rule) {
                $subject = Subject::query()->where('id', $rule['subject_id'])->where('course_id', $course->id)->first();

                if (! $subject) {
                    $validator->errors()->add('rules', __('Invalid subject for this course.'));

                    return;
                }
            }

            // Insufficient question counts are not rejected here - ExamService::create()
            // takes whatever is available and reports the shortfall back as a flash warning.
        });
    }
}
