<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'transaction_reference' => ['required', 'string', 'max:255'],
        ];
    }
}
