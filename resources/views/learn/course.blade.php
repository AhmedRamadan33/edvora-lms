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
