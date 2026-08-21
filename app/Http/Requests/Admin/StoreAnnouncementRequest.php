<?php

namespace App\Http\Requests\Admin;

use App\Repositories\UserRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasRole('admin');
    }

    public function rules(): array
    {
        $studentIds = app(UserRepository::class)->allStudents()->pluck('id')->all();

        return [
            'subject' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
            'audience' => ['required', 'in:all,selected'],
            'student_ids' => ['required_if:audience,selected', 'array'],
            'student_ids.*' => ['integer', Rule::in($studentIds)],
        ];
    }
}
