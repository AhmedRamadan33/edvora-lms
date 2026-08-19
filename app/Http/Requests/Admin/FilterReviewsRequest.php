<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FilterReviewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->filled('search') ? trim((string) $this->input('search')) : null,
            'status' => $this->filled('status') ? $this->input('status') : null,
            'course_id' => $this->filled('course_id') ? $this->input('course_id') : null,
            'rating' => $this->filled('rating') ? $this->input('rating') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:approved,rejected'],
            'course_id' => ['nullable', 'integer'],
            'rating' => ['nullable', 'in:1,2,3,4,5'],
        ];
    }
}
