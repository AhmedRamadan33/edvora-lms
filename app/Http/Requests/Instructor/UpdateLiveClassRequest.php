<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class UpdateLiveClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        $liveClass = $this->route('liveClass');

        return $this->user()
            && $liveClass
            && ($liveClass->instructor_id === $this->user()->id || $this->user()->hasRole('admin'));
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('scheduled_at')) {
            $this->merge([
                'scheduled_at' => Carbon::parse($this->input('scheduled_at'), config('edvora.display_timezone', 'Africa/Cairo'))->utc(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
        ];
    }
}
