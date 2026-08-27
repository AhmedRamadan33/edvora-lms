@extends('layouts.app')
@section('title', __('Certificate verification').' - '.\App\Services\SettingService::platformName())
@section('robots', 'noindex, follow')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="ed-panel p-4 p-md-5 text-center">
            @if($certificate)
                <div class="mb-3">
                    <span class="badge text-bg-success fs-6 px-3 py-2">
                        <i class="bi bi-patch-check-fill me-1"></i> {{ __('Valid certificate') }}
                    </span>
                </div>
                <h1 class="h3 mb-4">{{ __('Certificate verification') }}</h1>
                <div class="text-start mx-auto" style="max-width: 480px;">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">{{ __('Student') }}</span>
                        <strong>{{ $certificate->user->name }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">{{ __('Course') }}</span>
                        <strong>{{ $certificate->course->translation()?->title }}</strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">{{ __('Issued on') }}</span>
                        <strong>{{ $certificate->issued_at->format('F d, Y') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">{{ __('Certificate ID') }}</span>
                        <strong>{{ $certificate->code }}</strong>
                    </div>
                </div>
            @else
                <div class="mb-3">
                    <span class="badge text-bg-danger fs-6 px-3 py-2">
                        <i class="bi bi-x-octagon-fill me-1"></i> {{ __('Certificate not found') }}
                    </span>
                </div>
                <h1 class="h3 mb-3">{{ __('Certificate verification') }}</h1>
                <p class="text-muted">{{ __('No certificate matches the code :code.', ['code' => $code]) }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
