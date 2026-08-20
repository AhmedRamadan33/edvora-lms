@extends('layouts.app')
@section('title', __('Log in'))
@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <div class="mb-4">
            <div class="ed-brand mb-2" style="font-size:1.4rem">Edvora</div>
            <h1>{{ __('Welcome back') }}</h1>
            <p class="text-muted mb-0">{{ __('Log in to continue your learning journey.') }}</p>
        </div>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Password') }}</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">{{ __('Remember me') }}</label>
            </div>
            <button class="btn btn-primary w-100 mb-3">{{ __('Log in') }}</button>
        </form>
        <div class="d-flex justify-content-between small">
            <a href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
            <a href="{{ route('register') }}">{{ __('Create account') }}</a>
        </div>
    </div>
</div>
@endsection
