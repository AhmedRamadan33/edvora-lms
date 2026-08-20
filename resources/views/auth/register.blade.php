@extends('layouts.app')
@section('title', __('Register'))
@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <div class="mb-4">
            <div class="ed-brand mb-2" style="font-size:1.4rem">Edvora</div>
            <h1>{{ __('Join Edvora') }}</h1>
            <p class="text-muted mb-0">{{ __('Learn from experts or start teaching today.') }}</p>
        </div>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ __('Name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Password') }}</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Confirm Password') }}</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label">{{ __('Account type') }}</label>
                <select name="account_type" class="form-select">
                    <option value="student">{{ __('Student') }}</option>
                    <option value="instructor">{{ __('Instructor') }}</option>
                </select>
            </div>
            <button class="btn btn-primary w-100 mb-3">{{ __('Create account') }}</button>
        </form>
        <div class="small text-center">
            <a href="{{ route('login') }}">{{ __('Already have an account? Log in') }}</a>
        </div>
    </div>
</div>
@endsection
