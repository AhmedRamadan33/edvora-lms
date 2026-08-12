<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'title_en' => ['required', 'string', 'max:180'],
            'title_ar' => ['required', 'string', 'max:180'],
            'body_en' => ['nullable', 'string'],
            'body_ar' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }
}
