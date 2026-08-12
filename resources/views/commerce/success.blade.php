@extends('layouts.app')
@section('title', __('Payment success'))
@section('content')
<div class="auth-shell">
    <div class="auth-card text-center">
        <div class="mb-3" style="font-size:2.5rem;color:var(--ed-accent)"><i class="bi bi-check-circle-fill"></i></div>
        <h1 class="mb-2">{{ __('Payment successful') }}</h1>
        <p class="text-muted mb-1">{{ __('Order') }}: <strong>{{ $order->number }}</strong></p>
        <p class="text-muted mb-1">
            {{ __('Amount') }}:
            <strong>{{ number_format($order->total, 2) }} {{ $order->currency }}</strong>
        </p>
        @if($order->payment)
            <p class="text-muted mb-4">
                {{ __('Paid with') }}:
                <strong>{{ strtoupper($order->payment->provider) }}</strong>
            </p>
        @else
            <p class="text-muted mb-4">{{ __('Your courses are unlocked. Start learning now.') }}</p>
        @endif
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="{{ route('student.dashboard') }}" class="btn btn-primary">{{ __('Go to my learning') }}</a>
            <a href="{{ route('courses.index') }}" class="btn btn-outline-primary">{{ __('Browse more courses') }}</a>
        </div>
    </div>
</div>
@endsection
