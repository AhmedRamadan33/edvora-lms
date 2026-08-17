<?php

namespace App\Http\Requests\Instructor;

use App\Models\BankQuestion;
use App\Models\Subject;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddExamQuestionsRequest extends FormRequest
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
            'rules' => ['required', 'array', 'min:1'],
            'rules.*.subject_id' => ['required', 'integer'],
            'rules.*.type' => ['required', Rule::in(BankQuestion::TYPES)],
            'rules.*.count' => ['required', 'integer', 'min:1', 'max:200'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $exam = $this->route('exam');
            $courseId = $exam?->course_id;

            foreach ($this->input('rules', []) as $rule) {
                if (! filled($rule['subject_id'] ?? null)) {
                    continue;
                }

                $exists = Subject::query()->where('id', $rule['subject_id'])->where('course_id', $courseId)->exists();

                if (! $exists) {
                    $validator->errors()->add('rules', __('Invalid subject for this course.'));

                    return;
                }
            }
        });
    }
}
