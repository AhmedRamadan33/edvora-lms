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
                <input class="form-control" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}">
            </div>
        @endforeach
        <div class="col-12">
            <div class="ed-panel p-3 bg-light border-0">
                <div class="fw-semibold mb-2">{{ __('Payment webhooks') }}</div>
                <div class="small text-muted mb-1">Stripe: <code>{{ url('/webhooks/stripe') }}</code></div>
                <div class="small text-muted mb-1">Paymob callback: <code>{{ url('/webhooks/paymob') }}</code></div>
                <div class="small text-muted">Paymob redirection: <code>{{ url('/checkout/paymob/return') }}</code></div>
            </div>
        </div>
        <div class="col-12 pt-2">
            <button class="btn btn-primary">{{ __('Save Settings') }}</button>
        </div>
    </div>
</form>
@endsection
