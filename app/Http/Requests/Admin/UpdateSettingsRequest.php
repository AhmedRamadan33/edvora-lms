<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'platform_name' => ['required', 'string', 'max:120'],
            'default_commission' => ['required', 'numeric', 'min:0', 'max:100'],
            'currency' => ['required', 'string', 'size:3'],
            'bunny_library_id' => ['nullable', 'string'],
            'bunny_api_key' => ['nullable', 'string'],
            'bunny_cdn_hostname' => ['nullable', 'string'],
            'bunny_token_key' => ['nullable', 'string'],
            'stripe_key' => ['nullable', 'string'],
            'stripe_secret' => ['nullable', 'string'],
            'stripe_webhook_secret' => ['nullable', 'string'],
            'paymob_api_key' => ['nullable', 'string'],
            'paymob_integration_id' => ['nullable', 'string'],
            'paymob_iframe_id' => ['nullable', 'string'],
            'paymob_hmac_secret' => ['nullable', 'string'],
        ];
    }
}
