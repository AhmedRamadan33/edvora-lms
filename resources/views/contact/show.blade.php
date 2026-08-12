@extends('layouts.app')
@section('title', __('Contact'))
@section('content')
<div class="ed-page-head mb-4">
    <div>
        <h1 class="mb-1" style="font-size:clamp(2rem,4vw,2.8rem)">{{ __('Contact us') }}</h1>
        <p class="text-muted mb-0">{{ __('Have a question? Send us a message and we will get back to you.') }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('contact.store') }}" class="ed-panel p-4 p-lg-5">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Name') }}</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('Subject') }}</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('Message') }}</label>
                    <textarea name="message" class="form-control" rows="6" required>{{ old('message') }}</textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">{{ __('Send message') }}</button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-lg-5">
        <div class="ed-panel p-4 h-100">
            <h2 class="h5 mb-3">{{ __('Support') }}</h2>
            <p class="text-muted">{{ __('For partnerships, instructor onboarding, or payment support, reach out anytime.') }}</p>
            <div class="d-grid gap-2 mt-4">
                <div><strong>{{ __('Email') }}</strong><div class="text-muted">support@edvora.test</div></div>
                <div><strong>{{ __('Hours') }}</strong><div class="text-muted">{{ __('Sunday - Thursday, 10:00 - 18:00') }}</div></div>
            </div>
        </div>
    </div>
</div>
@endsection
