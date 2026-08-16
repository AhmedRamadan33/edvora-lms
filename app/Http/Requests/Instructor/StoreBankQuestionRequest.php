<?php

namespace App\Http\Requests\Instructor;

use App\Models\Subject;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreBankQuestionRequest extends FormRequest
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
            'type' => ['required', 'in:mcq_single,true_false,matching,fill_blank,essay'],
            'question' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
            'subject_id' => ['required', 'integer'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'is_active' => ['nullable', 'boolean'],

            'choices' => ['required_if:type,mcq_single', 'array', 'min:2'],
            'choices.*.text' => ['required_with:choices', 'string'],
            'choices.*.is_correct' => ['nullable', 'boolean'],
            'choices.*.image' => ['nullable', 'image', 'max:5120'],

            'true_false_answer' => ['required_if:type,true_false', 'in:true,false'],

            'matches' => ['required_if:type,matching', 'array', 'min:2'],
            'matches.*.prompt' => ['required_with:matches', 'string'],
            'matches.*.match' => ['required_with:matches', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('type') === 'mcq_single') {
                $choices = collect($this->input('choices', []))
                    ->filter(fn ($choice) => filled($choice['text'] ?? null));
                $correctCount = $choices->filter(fn ($choice) => (bool) ($choice['is_correct'] ?? false))->count();

                if ($correctCount !== 1) {
                    $validator->errors()->add('choices', __('Choose exactly one correct answer.'));
                }
            }

            $subjectId = $this->input('subject_id');
            $course = $this->route('course');

            if (filled($subjectId) && $course && ! Subject::query()->where('id', $subjectId)->where('course_id', $course->id)->exists()) {
                $validator->errors()->add('subject_id', __('Invalid subject for this course.'));
            }
        });
    }
}
