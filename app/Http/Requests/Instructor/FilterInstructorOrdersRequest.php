<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class FilterInstructorOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['instructor', 'admin']) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->filled('search') ? trim((string) $this->input('search')) : null,
            'payment_method' => $this->filled('payment_method') ? $this->input('payment_method') : null,
            'status' => $this->filled('status') ? $this->input('status') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'payment_method' => ['nullable', 'in:stripe,paymob'],
            'status' => ['nullable', 'in:pending,paid,failed,refunded'],
        ];
    }
}
