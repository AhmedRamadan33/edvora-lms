<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class StartChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && ($user->hasRole('instructor') || $user->hasRole('admin'));
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer'],
        ];
    }
}
