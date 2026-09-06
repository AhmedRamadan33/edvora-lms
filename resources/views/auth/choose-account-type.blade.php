@extends('layouts.app')
@section('title', __('Choose account type').' - '.\App\Services\SettingService::platformName())
@section('robots', 'noindex, nofollow')
@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <div class="mb-4">
            <div class="ed-brand mb-2" style="font-size:1.4rem">Edvora</div>
            <h1>{{ __('How do you want to use :platform?', ['platform' => \App\Services\SettingService::platformName()]) }}</h1>
            <p class="text-muted mb-0">{{ __('Choose the option that fits you best.') }}</p>
        </div>

        <form method="POST" action="{{ route('social.choose-type') }}">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label class="ed-choice-option">
                        <input type="radio" name="account_type" value="student" checked>
                        <span class="ed-choice-option__body">
                            <i class="bi bi-mortarboard ed-choice-option__icon d-block"></i>
                            <span class="fw-semibold d-block">{{ __('Student') }}</span>
                            <span class="small text-muted">{{ __('Learn from expert instructors') }}</span>
                        </span>
                    </label>
                </div>
                <div class="col-6">
                    <label class="ed-choice-option">
                        <input type="radio" name="account_type" value="instructor">
                        <span class="ed-choice-option__body">
                            <i class="bi bi-easel2 ed-choice-option__icon d-block"></i>
                            <span class="fw-semibold d-block">{{ __('Instructor') }}</span>
                            <span class="small text-muted">{{ __('Create and sell your own courses') }}</span>
                        </span>
                    </label>
                </div>
            </div>
            <button class="btn btn-primary w-100">{{ __('Continue') }}</button>
        </form>
    </div>
</div>
@endsection
