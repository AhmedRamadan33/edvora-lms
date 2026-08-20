@extends('layouts.panel')
@section('heading', __('My learning'))
@section('sidebar')
@include('student.partials.nav')
@endsection
@section('content')
@if($upcomingLiveClasses->isNotEmpty())
<div class="ed-panel p-3 mb-4">
    <h2 class="h6 mb-3"><i class="bi bi-camera-video me-2"></i>{{ __('Upcoming Live Classes') }}</h2>
    <ul class="list-group list-group-flush">
        @foreach($upcomingLiveClasses as $liveClass)
            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2 px-0">
                <span>
                    <strong>{{ $liveClass->title }}</strong>
                    <span class="small text-muted">— {{ $liveClass->course->translation()?->title }}</span>
                    <br>
                    <small class="text-muted">{{ $liveClass->scheduledAtLocal()->format('Y-m-d H:i') }}</small>
                </span>
                <a href="{{ route('learn.course', $liveClass->course) }}" class="btn btn-sm btn-outline-primary">{{ __('View course') }}</a>
            </li>
        @endforeach
    </ul>
</div>
@endif
<div class="row g-4">
@forelse($enrollments as $enrollment)
<div class="col-md-6 col-xl-4">
    <div class="ed-panel h-100 p-3">
        <div class="d-flex justify-content-between align-items-start mb-1">
            <div class="small text-muted">{{ $enrollment->course->instructor?->name }}</div>
            @if($enrollment->completed_at)
                <span class="ed-status is-approved">{{ __('Completed') }}</span>
            @endif
        </div>
        <h3 class="h5 mb-3">{{ $enrollment->course->translation()?->title }}</h3>
        <div class="progress mb-2" role="progressbar" style="height:0.55rem;border-radius:999px">
            <div class="progress-bar" style="width: {{ $enrollment->progress_percent }}%; background:var(--ed-accent)"></div>
        </div>
        <div class="small text-muted mb-3">{{ $enrollment->progress_percent }}% {{ __('complete') }}</div>
        <div class="d-flex gap-2">
            <a href="{{ route('learn.course', $enrollment->course) }}" class="btn btn-primary btn-sm">
                {{ $enrollment->completed_at ? __('Review course') : __('Continue') }}
            </a>
            @if($enrollment->completed_at)
                <a href="{{ route('student.certificates.index') }}" class="btn btn-outline-primary btn-sm">{{ __('View certificate') }}</a>
            @endif
        </div>
    </div>
</div>
@empty
<div class="col-12">
    <div class="ed-panel p-5 text-center">
        <h2 class="h4 mb-2">{{ __('No enrollments yet.') }}</h2>
        <p class="text-muted mb-3">{{ __('Explore the catalog and start your first course.') }}</p>
        <a href="{{ route('courses.index') }}" class="btn btn-primary">{{ __('Browse catalog') }}</a>
    </div>
</div>
@endforelse
</div>
@endsection
