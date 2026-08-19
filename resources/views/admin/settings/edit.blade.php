@extends('layouts.panel')
@section('heading', __('Settings'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Platform settings') }}</h2>
        <p>{{ __('Configure branding, commission, video, and payment providers.') }}</p>
    </div>
</div>

@php
    $badges = [];
    foreach (['stripe', 'paymob', 'paytabs', 'paypal'] as $gateway) {
        $isEnabled = (bool) ($settings["{$gateway}_enabled"] ?? true);
        $isConfigured = $gateways[$gateway]['configured'];
        $badges[$gateway] = [
            'class' => ! $isEnabled ? 'secondary' : ($isConfigured ? 'success' : 'warning'),
            'text' => ! $isEnabled ? __('Disabled') : ($isConfigured ? __('Live') : __('Demo')),
        ];
    }
@endphp

<form method="POST" action="{{ route('admin.settings.update') }}" class="ed-panel p-4">
    @csrf @method('PUT')

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-basic-btn" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" role="tab">
                {{ __('Basic settings') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-vdocipher-btn" data-bs-toggle="tab" data-bs-target="#tab-vdocipher" type="button" role="tab">
                VdoCipher
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-stripe-btn" data-bs-toggle="tab" data-bs-target="#tab-stripe" type="button" role="tab">
                Stripe <span class="badge text-bg-{{ $badges['stripe']['class'] }} ms-1">{{ $badges['stripe']['text'] }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-paymob-btn" data-bs-toggle="tab" data-bs-target="#tab-paymob" type="button" role="tab">
                Paymob <span class="badge text-bg-{{ $badges['paymob']['class'] }} ms-1">{{ $badges['paymob']['text'] }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-paytabs-btn" data-bs-toggle="tab" data-bs-target="#tab-paytabs" type="button" role="tab">
                PayTabs <span class="badge text-bg-{{ $badges['paytabs']['class'] }} ms-1">{{ $badges['paytabs']['text'] }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-paypal-btn" data-bs-toggle="tab" data-bs-target="#tab-paypal" type="button" role="tab">
                {{ __('PayPal') }} <span class="badge text-bg-{{ $badges['paypal']['class'] }} ms-1">{{ $badges['paypal']['text'] }}</span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
            <div class="row g-3">
                @foreach([
                    'platform_name' => 'Platform name',
                    'platform_email' => 'Platform email',
                    'platform_phone' => 'Platform phone',
                    'default_commission' => 'Default commission %',
                    'currency' => 'Currency',
                ] as $key => $label)
                    <div class="col-md-6">
                        <label class="form-label">{{ __($label) }}</label>
                        <input class="form-control" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}"
                            @if($key === 'platform_email') type="email" @endif
                            @if($key === 'currency') list="edvora-currencies" maxlength="3" style="text-transform:uppercase" @endif>
                        @if($key === 'currency')
                            <datalist id="edvora-currencies">
                                @foreach(config('edvora.supported_currencies') as $code)
                                    <option value="{{ $code }}"></option>
                                @endforeach
                            </datalist>
                            <div class="form-text">{{ __('Official platform currency for courses, checkout, Stripe, and Paymob. Changing it updates all courses.') }}</div>
                        @endif
                        @if($key === 'platform_phone')
                            <div class="form-text">{{ __('Shown in the public site footer.') }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="tab-pane fade" id="tab-vdocipher" role="tabpanel">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('VdoCipher API secret') }}</label>
                    <x-secret-input name="vdocipher_api_secret" :value="old('vdocipher_api_secret', $settings['vdocipher_api_secret'] ?? '')" />
                </div>
            </div>
            {{-- <div class="ed-panel p-3 bg-light border-0 mt-3">
                <div class="small text-muted mb-1">{{ __('Webhook endpoint') }}: <code>{{ rtrim(config('app.url'), '/') }}/webhooks/vdocipher?token=VDOCIPHER_WEBHOOK_TOKEN</code></div>
            </div> --}}
        </div>

        <div class="tab-pane fade" id="tab-stripe" role="tabpanel">
            <div class="form-check form-switch mb-3">
                <input type="hidden" name="stripe_enabled" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="stripe_enabled" name="stripe_enabled" value="1" @checked($settings['stripe_enabled'] ?? true)>
                <label class="form-check-label" for="stripe_enabled">{{ __('Show this payment method to students') }}</label>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Stripe publishable key') }}</label>
                    <input class="form-control" name="stripe_key" value="{{ old('stripe_key', $settings['stripe_key'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Stripe secret') }}</label>
                    <x-secret-input name="stripe_secret" :value="old('stripe_secret', $settings['stripe_secret'] ?? '')" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Stripe webhook secret') }}</label>
                    <x-secret-input name="stripe_webhook_secret" :value="old('stripe_webhook_secret', $settings['stripe_webhook_secret'] ?? '')" />
                </div>
            </div>
            {{-- <div class="ed-panel p-3 bg-light border-0 mt-3">
                <div class="small text-muted mb-1">{{ __('Webhook endpoint') }}: <code>{{ rtrim(config('app.url'), '/') }}/webhooks/stripe</code></div>
                <div class="small text-muted">{{ __('Events') }}: checkout.session.completed, checkout.session.expired, checkout.session.async_payment_succeeded, checkout.session.async_payment_failed, payment_intent.payment_failed</div>
            </div> --}}
        </div>

        <div class="tab-pane fade" id="tab-paymob" role="tabpanel">
            <div class="form-check form-switch mb-3">
                <input type="hidden" name="paymob_enabled" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="paymob_enabled" name="paymob_enabled" value="1" @checked($settings['paymob_enabled'] ?? true)>
                <label class="form-check-label" for="paymob_enabled">{{ __('Show this payment method to students') }}</label>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Paymob API key') }}</label>
                    <x-secret-input name="paymob_api_key" :value="old('paymob_api_key', $settings['paymob_api_key'] ?? '')" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Paymob integration ID') }}</label>
                    <input class="form-control" name="paymob_integration_id" value="{{ old('paymob_integration_id', $settings['paymob_integration_id'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Paymob iframe ID') }}</label>
                    <input class="form-control" name="paymob_iframe_id" value="{{ old('paymob_iframe_id', $settings['paymob_iframe_id'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Paymob HMAC') }}</label>
                    <x-secret-input name="paymob_hmac_secret" :value="old('paymob_hmac_secret', $settings['paymob_hmac_secret'] ?? '')" />
                </div>
            </div>
            {{-- <div class="ed-panel p-3 bg-light border-0 mt-3">
                <div class="small text-muted mb-1">{{ __('Transaction processed callback') }}: <code>{{ rtrim(config('app.url'), '/') }}/webhooks/paymob</code></div>
                <div class="small text-muted">{{ __('Redirection URL') }}: <code>{{ rtrim(config('app.url'), '/') }}/checkout/paymob/return</code></div>
            </div> --}}
        </div>

        <div class="tab-pane fade" id="tab-paytabs" role="tabpanel">
            <div class="form-check form-switch mb-3">
                <input type="hidden" name="paytabs_enabled" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="paytabs_enabled" name="paytabs_enabled" value="1" @checked($settings['paytabs_enabled'] ?? true)>
                <label class="form-check-label" for="paytabs_enabled">{{ __('Show this payment method to students') }}</label>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('PayTabs profile ID') }}</label>
                    <input class="form-control" name="paytabs_profile_id" value="{{ old('paytabs_profile_id', $settings['paytabs_profile_id'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('PayTabs server key') }}</label>
                    <x-secret-input name="paytabs_server_key" :value="old('paytabs_server_key', $settings['paytabs_server_key'] ?? '')" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('PayTabs region') }}</label>
                    @php($currentRegion = old('paytabs_region', $settings['paytabs_region'] ?? 'egypt'))
                    <select class="form-select" name="paytabs_region">
                        @foreach(['egypt' => 'Egypt', 'uae' => 'UAE', 'ksa' => 'Saudi Arabia', 'oman' => 'Oman', 'jordan' => 'Jordan', 'kuwait' => 'Kuwait', 'iraq' => 'Iraq', 'morocco' => 'Morocco', 'qatar' => 'Qatar', 'global' => 'Global'] as $value => $label)
                            <option value="{{ $value }}" @selected($currentRegion === $value)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            {{-- <div class="ed-panel p-3 bg-light border-0 mt-3">
                <div class="small text-muted mb-1">{{ __('Callback / IPN URL') }}: <code>{{ rtrim(config('app.url'), '/') }}/webhooks/paytabs</code></div>
                <div class="small text-muted">{{ __('Return URL') }}: <code>{{ rtrim(config('app.url'), '/') }}/checkout/paytabs/return</code></div>
            </div> --}}
        </div>

        <div class="tab-pane fade" id="tab-paypal" role="tabpanel">
            <div class="form-check form-switch mb-3">
                <input type="hidden" name="paypal_enabled" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="paypal_enabled" name="paypal_enabled" value="1" @checked($settings['paypal_enabled'] ?? true)>
                <label class="form-check-label" for="paypal_enabled">{{ __('Show this payment method to students') }}</label>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('PayPal client ID') }}</label>
                    <input class="form-control" name="paypal_client_id" value="{{ old('paypal_client_id', $settings['paypal_client_id'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('PayPal secret') }}</label>
                    <x-secret-input name="paypal_secret" :value="old('paypal_secret', $settings['paypal_secret'] ?? '')" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('PayPal webhook ID') }}</label>
                    <input class="form-control" name="paypal_webhook_id" value="{{ old('paypal_webhook_id', $settings['paypal_webhook_id'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('PayPal mode') }}</label>
                    @php($currentMode = old('paypal_mode', $settings['paypal_mode'] ?? 'sandbox'))
                    <select class="form-select" name="paypal_mode">
                        <option value="sandbox" @selected($currentMode === 'sandbox')>{{ __('Sandbox') }}</option>
                        <option value="live" @selected($currentMode === 'live')>{{ __('Live') }}</option>
                    </select>
                </div>
            </div>
            {{-- <div class="ed-panel p-3 bg-light border-0 mt-3">
                <div class="small text-muted mb-1">{{ __('Webhook endpoint') }}: <code>{{ rtrim(config('app.url'), '/') }}/webhooks/paypal</code></div>
                <div class="small text-muted">{{ __('Events') }}: CHECKOUT.ORDER.APPROVED, PAYMENT.CAPTURE.COMPLETED</div>
            </div> --}}
        </div>
    </div>

    <div class="pt-3">
        <button class="btn btn-primary">{{ __('Save Settings') }}</button>
    </div>
</form>
@endsection
