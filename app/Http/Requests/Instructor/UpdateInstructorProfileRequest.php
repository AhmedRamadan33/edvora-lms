<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstructorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'headline' => ['nullable', 'string', 'max:180'],
            'about' => ['nullable', 'string'],
            'website' => ['nullable', 'url'],
        ];
    }
}
