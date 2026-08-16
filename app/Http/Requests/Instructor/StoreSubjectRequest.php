<?php

namespace App\Http\Requests\Instructor;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['instructor', 'admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('subjects', 'name')->where(fn ($query) => $query->where('course_id', $this->input('course_id'))),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $course = Course::query()->find($this->input('course_id'));

            if (! $course || ! ($course->instructor_id === $this->user()->id || $this->user()->hasRole('admin'))) {
                $validator->errors()->add('course_id', __('Invalid course.'));
            }
        });
    }
}
