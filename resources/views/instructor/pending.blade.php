@extends('layouts.app')
@section('title', __('Instructor application status').' - '.\App\Services\SettingService::platformName())
@section('robots', 'noindex, nofollow')
@section('content')
<div class="auth-shell">
    <div class="auth-card text-center">
        <div class="ed-brand mb-3 justify-content-center" style="font-size:1.4rem">Edvora</div>

        @if ($profile?->status === 'rejected')
            <i class="bi bi-x-circle text-danger" style="font-size:2.5rem"></i>
            <h1 class="h4 mt-3">{{ __('Your instructor application was not approved') }}</h1>
            @if ($profile->rejection_reason)
                <p class="text-muted">{{ $profile->rejection_reason }}</p>
            @endif
        @else
            <i class="bi bi-hourglass-split text-warning" style="font-size:2.5rem"></i>
            <h1 class="h4 mt-3">{{ __('Your instructor application is under review') }}</h1>
            <p class="text-muted">{{ __("We'll notify you by email as soon as an admin reviews your application.") }}</p>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">{{ __('Log Out') }}</button>
        </form>
    </div>
</div>
@endsection
