<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestPayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', Rule::in(['paypal', 'bank_transfer', 'e_wallet'])],
            'paypal_email' => ['required_if:method,paypal', 'nullable', 'email', 'max:255'],
            'bank_name' => ['required_if:method,bank_transfer', 'nullable', 'string', 'max:255'],
            'account_number' => ['required_if:method,bank_transfer', 'nullable', 'string', 'max:100'],
            'account_holder' => ['required_if:method,bank_transfer', 'nullable', 'string', 'max:255'],
            'wallet_number' => ['required_if:method,e_wallet', 'nullable', 'string', 'max:50'],
        ];
    }
}
