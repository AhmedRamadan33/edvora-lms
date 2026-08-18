<?php

namespace App\Http\Requests\Instructor;

use App\Models\BankQuestion;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class GradeExamAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $exam = $this->route('exam');
        $attempt = $this->route('attempt');

        return $this->user()
            && $exam
            && $attempt
            && $attempt->exam_id === $exam->id
            && ($exam->course->instructor_id === $this->user()->id || $this->user()->hasRole('admin'));
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.bank_question_id' => ['required', 'integer'],
            'answers.*.points_awarded' => ['required', 'integer', 'min:0'],
            'answers.*.instructor_feedback' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $exam = $this->route('exam');

            $manuallyGraded = $exam->questions()
                ->whereIn('type', BankQuestion::MANUALLY_GRADED_TYPES)
                ->pluck('points', 'bank_questions.id');

            foreach ($this->input('answers', []) as $row) {
                $questionId = (int) ($row['bank_question_id'] ?? 0);

                if (! $manuallyGraded->has($questionId)) {
                    $validator->errors()->add('answers', __('One of the submitted answers does not belong to this exam or is not manually graded.'));

                    return;
                }

                $max = $manuallyGraded->get($questionId);

                if ((int) ($row['points_awarded'] ?? -1) > $max) {
                    $validator->errors()->add('answers', __("Points awarded cannot exceed the question's maximum points."));

                    return;
                }
            }
        });
    }
}
