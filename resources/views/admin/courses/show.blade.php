@extends('layouts.panel')
@section('heading', __('Review course'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ $course->translation()?->title }}</h2>
        <p>{{ $course->instructor->name }} · <span class="ed-status is-{{ $course->status }}">{{ __status($course->status) }}</span></p>
    </div>
    <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-primary btn-sm">{{ __('Back') }}</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="ed-panel p-4 mb-4">
            <h3 class="h5 mb-3">{{ __('About this course') }}</h3>
            <p class="text-secondary mb-0">{!! nl2br(e($course->translation()?->description)) !!}</p>
        </div>

        <div class="ed-panel p-4">
            <h3 class="h5 mb-3">{{ __('Curriculum') }}</h3>
            @forelse($course->sections as $section)
                <div class="mb-3">
                    <div class="fw-semibold mb-2">{{ $section->title }}</div>
                    <ul class="list-group list-group-flush border rounded-3">
                        @foreach($section->lessons as $lesson)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $lesson->title }}</span>
                                <span class="text-muted small">{{ $lesson->type }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="text-muted mb-0">{{ __('No lessons yet.') }}</p>
            @endforelse
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ed-panel p-4">
            <h3 class="h6 mb-3">{{ __('Moderation') }}</h3>
            <form method="POST" action="{{ route('admin.courses.approve', $course) }}" class="mb-3">
                @csrf
                <button class="btn btn-success w-100">{{ __('Approve & publish') }}</button>
            </form>
            <form method="POST" action="{{ route('admin.courses.reject', $course) }}">
                @csrf
                <textarea name="rejection_reason" class="form-control mb-2" rows="4" required placeholder="{{ __('Rejection reason') }}"></textarea>
                <button class="btn btn-outline-primary w-100">{{ __('Reject') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
