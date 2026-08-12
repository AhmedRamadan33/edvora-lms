@extends('layouts.panel')

@section('title', __('Profile settings'))
@section('heading', __('Profile settings'))

@section('sidebar')
    @include('partials.panel-sidebar')
@endsection

@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Profile settings') }}</h2>
        <p>{{ __('Manage your account details and password.') }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="ed-panel p-4 mb-4">
            <h3 class="h5 mb-3">{{ __('Profile') }}</h3>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="form-label">{{ __('Name') }}</label>
                    <input name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                </div>
                <button class="btn btn-primary">{{ __('Save') }}</button>
            </form>
        </div>

        <div class="ed-panel p-4">
            <h3 class="h5 mb-3">{{ __('Update Password') }}</h3>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT')
                <input type="password" name="current_password" class="form-control mb-2" placeholder="{{ __('Current Password') }}" required>
                <input type="password" name="password" class="form-control mb-2" placeholder="{{ __('New Password') }}" required>
                <input type="password" name="password_confirmation" class="form-control mb-2" placeholder="{{ __('Confirm Password') }}" required>
                <button class="btn btn-primary">{{ __('Update Password') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
