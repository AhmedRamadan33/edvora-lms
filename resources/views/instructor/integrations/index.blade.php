@extends('layouts.panel')
@section('heading', __('Integrations'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Live class accounts') }}</h2>
        <p>{{ __('Connect your own Zoom and Google accounts to host live classes for your courses.') }}</p>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="ed-panel p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h3 class="h5 mb-0"><i class="bi bi-camera-video me-2"></i>{{ __('Zoom') }}</h3>
                @if($zoomConnection)
                    <span class="badge text-bg-success">{{ __('Connected') }}</span>
                @else
                    <span class="badge text-bg-secondary">{{ __('Not connected') }}</span>
                @endif
            </div>

            @if(! $zoomConfigured)
                <p class="text-muted small mb-0">{{ __('Zoom is not configured on this platform yet. Contact the platform admin.') }}</p>
            @elseif($zoomConnection)
                <p class="text-muted small mb-3">{{ __('Connected as :email', ['email' => $zoomConnection->provider_email]) }}</p>
                <form method="POST" action="{{ route('instructor.integrations.zoom.disconnect') }}" data-confirm-message="{{ __('Disconnect your Zoom account? Existing scheduled classes will keep working, but you will need to reconnect before scheduling new ones.') }}">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm">{{ __('Disconnect') }}</button>
                </form>
            @else
                <p class="text-muted small mb-3">{{ __('Not connected yet.') }}</p>
                <a href="{{ route('instructor.integrations.zoom.connect') }}" class="btn btn-primary btn-sm">{{ __('Connect Zoom account') }}</a>
            @endif
        </div>
    </div>

    <div class="col-md-6">
        <div class="ed-panel p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h3 class="h5 mb-0"><i class="bi bi-camera-video-fill me-2"></i>{{ __('Google Meet') }}</h3>
                @if($googleConnection)
                    <span class="badge text-bg-success">{{ __('Connected') }}</span>
                @else
                    <span class="badge text-bg-secondary">{{ __('Not connected') }}</span>
                @endif
            </div>

            @if(! $googleConfigured)
                <p class="text-muted small mb-0">{{ __('Google Meet is not configured on this platform yet. Contact the platform admin.') }}</p>
            @elseif($googleConnection)
                <p class="text-muted small mb-3">{{ __('Connected as :email', ['email' => $googleConnection->provider_email]) }}</p>
                <form method="POST" action="{{ route('instructor.integrations.google.disconnect') }}" data-confirm-message="{{ __('Disconnect your Google account? Existing scheduled classes will keep working, but you will need to reconnect before scheduling new ones.') }}">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm">{{ __('Disconnect') }}</button>
                </form>
            @else
                <p class="text-muted small mb-3">{{ __('Not connected yet.') }}</p>
                <a href="{{ route('instructor.integrations.google.connect') }}" class="btn btn-primary btn-sm">{{ __('Connect Google account') }}</a>
            @endif
        </div>
    </div>
</div>
@endsection
