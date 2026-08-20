<?php

namespace App\Http\Requests\Admin;

use App\Services\PayPalService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('currency')) {
            $this->merge([
                'currency' => strtoupper((string) $this->input('currency')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'platform_name' => ['required', 'string', 'max:120'],
            'platform_email' => ['nullable', 'email', 'max:150'],
            'platform_phone' => ['nullable', 'string', 'max:30'],
            'default_commission' => ['required', 'numeric', 'min:0', 'max:100'],
            'currency' => ['required', 'string', 'size:3', Rule::in(config('edvora.supported_currencies', ['EGP', 'USD']))],
            'vdocipher_api_secret' => ['nullable', 'string'],
            'stripe_key' => ['nullable', 'string'],
            'stripe_secret' => ['nullable', 'string'],
            'stripe_webhook_secret' => ['nullable', 'string'],
            'paymob_api_key' => ['nullable', 'string'],
            'paymob_integration_id' => ['nullable', 'string'],
            'paymob_iframe_id' => ['nullable', 'string'],
            'paymob_hmac_secret' => ['nullable', 'string'],
            'paytabs_profile_id' => ['nullable', 'string'],
            'paytabs_server_key' => ['nullable', 'string'],
            'paytabs_region' => ['nullable', Rule::in(['egypt', 'uae', 'ksa', 'oman', 'jordan', 'kuwait', 'iraq', 'morocco', 'qatar', 'global'])],
            'paypal_client_id' => ['nullable', 'string'],
            'paypal_secret' => ['nullable', 'string'],
            'paypal_webhook_id' => ['nullable', 'string'],
            'paypal_mode' => ['nullable', Rule::in(['sandbox', 'live'])],
            'paypal_settlement_currency' => ['nullable', 'string', 'size:3', Rule::in(PayPalService::supportedCurrencies())],
            'paypal_exchange_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'stripe_enabled' => ['nullable', 'in:0,1'],
            'paymob_enabled' => ['nullable', 'in:0,1'],
            'paytabs_enabled' => ['nullable', 'in:0,1'],
            'paypal_enabled' => ['nullable', 'in:0,1'],
        ];
    }
}
