@extends('layouts.app')
@section('title', __('Checkout'))
@section('content')
<div class="ed-section__head mb-4">
    <div>
        <h1 class="mb-1" style="font-size:clamp(2rem,4vw,2.6rem)">{{ __('Checkout') }}</h1>
        <p class="text-muted mb-0">{{ __('Complete your purchase securely.') }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="ed-panel p-4">
            <h2 class="h5 mb-3">{{ __('Order summary') }}</h2>
            <ul class="list-group list-group-flush mb-3">
                @foreach($items as $item)
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>{{ $item->course->translation()?->title }}</span>
                        <span>{{ number_format($item->course->price, 2) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="d-flex justify-content-between"><span>{{ __('Subtotal') }}</span><strong>{{ money($subtotal, $currency) }}</strong></div>
            <div class="d-flex justify-content-between"><span>{{ __('Discount') }}</span><strong>- {{ money($discount, $currency) }}</strong></div>
            <div class="d-flex justify-content-between fs-4 mt-3 pt-3 border-top">
                <span>{{ __('Total') }}</span>
                <strong>{{ money($total, $currency) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <form method="POST" action="{{ route('checkout.coupon') }}" class="ed-panel p-4 mb-3">
            @csrf
            <label class="form-label">{{ __('Coupon') }}</label>
            <div class="input-group">
                <input name="code" class="form-control" value="{{ $coupon?->code }}" placeholder="WELCOME20">
                <button class="btn btn-outline-primary">{{ __('Apply') }}</button>
            </div>
        </form>

        <form method="POST" action="{{ route('checkout.pay') }}" class="ed-panel p-4">
            @csrf
            <label class="form-label">{{ __('Payment method') }}</label>

            <div class="d-grid gap-2 mb-3">
                @if($providers['stripe']['enabled'])
                    <label class="ed-panel p-3 mb-0 d-flex align-items-center gap-3" style="cursor:pointer;">
                        <input type="radio" name="provider" value="stripe" class="form-check-input m-0" @checked(old('provider', 'stripe') === 'stripe') required>
                        <span class="flex-grow-1">
                            <strong>Stripe</strong>
                            <div class="small text-muted">
                                {{ $providers['stripe']['configured'] ? __('Cards via Stripe Checkout') : __('Demo mode (keys missing)') }}
                            </div>
                        </span>
                        <i class="bi bi-credit-card fs-4 text-primary"></i>
                    </label>
                @endif

                @if($providers['paymob']['enabled'])
                    <label class="ed-panel p-3 mb-0 d-flex align-items-center gap-3" style="cursor:pointer;">
                        <input type="radio" name="provider" value="paymob" class="form-check-input m-0" @checked(old('provider') === 'paymob' || ! $providers['stripe']['enabled']) required>
                        <span class="flex-grow-1">
                            <strong>Paymob</strong>
                            <div class="small text-muted">
                                {{ $providers['paymob']['configured'] ? __('Cards & wallets via Paymob') : __('Demo mode (keys missing)') }}
                            </div>
                        </span>
                        <i class="bi bi-wallet2 fs-4 text-primary"></i>
                    </label>
                @endif
            </div>

            @if(! $providers['stripe']['enabled'] && ! $providers['paymob']['enabled'])
                <div class="alert alert-danger mb-3">{{ __('No payment providers are configured.') }}</div>
            @else
                <button class="btn btn-primary w-100" @disabled(! $providers['stripe']['enabled'] && ! $providers['paymob']['enabled'])>
                    {{ $total <= 0 ? __('Complete enrollment') : __('Pay now') }}
                </button>
            @endif

            @if($demo_mode)
                <p class="small text-muted mt-3 mb-0">{{ __('Demo payments are enabled for local testing when gateway keys are missing.') }}</p>
            @else
                <p class="small text-muted mt-3 mb-0">{{ __('You will be redirected to a secure payment page.') }}</p>
            @endif
        </form>
    </div>
</div>
@endsection
