@extends('layouts.app')
@section('title', $course->translation()?->title)
@section('content')
<div class="ed-page-head">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h1 class="mb-0" style="font-size:clamp(1.8rem,3vw,2.4rem)">{{ $course->translation()?->title }}</h1>
            @if($enrollment?->completed_at)
                <span class="ed-status is-approved">{{ __('Completed') }}</span>
            @endif
        </div>
        <p class="text-muted mb-0">{{ __('Continue where you left off and track your progress.') }}</p>
    </div>
</div>

@php($visibleLiveClasses = $course->liveClasses->reject(fn ($liveClass) => in_array($liveClass->computedState(), ['cancelled', 'ended'], true)))
@if($visibleLiveClasses->isNotEmpty())
    <div class="ed-panel p-4 mb-4">
        <h2 class="h5 mb-3"><i class="bi bi-camera-video me-2"></i>{{ __('Live Classes') }}</h2>
        <ul class="list-group">
            @foreach($visibleLiveClasses as $liveClass)
                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>
                        <strong>{{ $liveClass->title }}</strong>
                        <span class="badge text-bg-light">{{ $liveClass->provider === 'zoom' ? __('Zoom') : __('Google Meet') }}</span>
                        @if($liveClass->computedState() === 'live')
                            <span class="ed-status is-active">{{ __('Live now') }}</span>
                        @endif
                        <br>
                        <small class="text-muted">{{ $liveClass->scheduled_at->format('Y-m-d H:i') }} · {{ $liveClass->duration_minutes }} {{ __('min') }}</small>
                    </span>
                    @if($liveClass->isJoinable())
                        <a href="{{ $liveClass->join_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-success">{{ __('Join now') }}</a>
                    @else
                        <span class="btn btn-sm btn-outline-secondary disabled">{{ __('Starts at :time', ['time' => $liveClass->scheduled_at->format('H:i')]) }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="learn-shell">
    @include('learn.partials.sidebar', ['activeLesson' => null])

    <div class="ed-panel p-4 p-lg-5">
        @if($enrollment?->completed_at)
            <div class="text-center py-3">
                <i class="bi bi-trophy-fill text-warning" style="font-size:3rem"></i>
                <h2 class="h4 mt-3 mb-2">{{ __('Course completed!') }}</h2>
                <p class="text-muted mb-4">{{ __('Congratulations! You have completed this course.') }}</p>
                <a href="{{ route('student.certificates.index') }}" class="btn btn-primary">
                    <i class="bi bi-award me-1"></i>{{ __('View your certificate') }}
                </a>
            </div>
        @else
            <h2 class="h4 mb-2">{{ __('Ready to learn?') }}</h2>
            <p class="text-muted mb-4">{{ __('Select a lesson from the sidebar to begin watching, reading, or taking a quiz.') }}</p>
            @php($firstLesson = $course->sections->first()?->lessons->first())
            @if($firstLesson)
                <a href="{{ route('learn.lesson', [$course, $firstLesson]) }}" class="btn btn-primary">{{ __('Start first lesson') }}</a>
            @endif
        @endif
    </div>
</div>
@endsection
