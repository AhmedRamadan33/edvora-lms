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

<form method="POST" action="{{ route('admin.settings.update') }}" class="ed-panel p-4">
    @csrf @method('PUT')
    <div class="row g-3">
        @foreach([
            'platform_name' => 'Platform name',
            'default_commission' => 'Default commission %',
            'currency' => 'Currency',
            'bunny_library_id' => 'Bunny library ID',
            'bunny_api_key' => 'Bunny API key',
            'bunny_cdn_hostname' => 'Bunny CDN hostname',
            'bunny_token_key' => 'Bunny token key',
            'stripe_key' => 'Stripe publishable key',
            'stripe_secret' => 'Stripe secret',
            'stripe_webhook_secret' => 'Stripe webhook secret',
            'paymob_api_key' => 'Paymob API key',
            'paymob_integration_id' => 'Paymob integration ID',
            'paymob_iframe_id' => 'Paymob iframe ID',
            'paymob_hmac_secret' => 'Paymob HMAC',
        ] as $key => $label)
            <div class="col-md-6">
                <label class="form-label">{{ __($label) }}</label>
                <input class="form-control" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}" @if($key === 'currency') list="edvora-currencies" maxlength="3" style="text-transform:uppercase" @endif>
                @if($key === 'currency')
                    <datalist id="edvora-currencies">
                        @foreach(config('edvora.supported_currencies') as $code)
                            <option value="{{ $code }}"></option>
                        @endforeach
                    </datalist>
                    <div class="form-text">{{ __('Official platform currency for courses, checkout, Stripe, and Paymob. Changing it updates all courses.') }}</div>
                @endif
            </div>
        @endforeach
        <div class="col-12">
            <div class="ed-panel p-3 bg-light border-0">
                <div class="fw-semibold mb-2">{{ __('Payment webhooks') }}</div>
                <div class="small text-muted mb-2">{{ __('Use these URLs in Stripe and Paymob dashboards. APP_URL must match your live domain.') }}</div>
                <div class="small text-muted mb-1">Stripe webhook: <code>{{ rtrim(config('app.url'), '/') }}/webhooks/stripe</code></div>
                <div class="small text-muted mb-1">Paymob callback: <code>{{ rtrim(config('app.url'), '/') }}/webhooks/paymob</code></div>
                <div class="small text-muted mb-1">Paymob redirection: <code>{{ rtrim(config('app.url'), '/') }}/checkout/paymob/return</code></div>
                <div class="small text-muted mt-2">Live domain example: <code>https://edvora.codeversetechno.com</code></div>
            </div>
        </div>
        <div class="col-12 pt-2">
            <button class="btn btn-primary">{{ __('Save Settings') }}</button>
        </div>
    </div>
</form>
@endsection
