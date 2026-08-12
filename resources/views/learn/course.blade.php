@extends('layouts.app')
@section('title', $course->translation()?->title)
@section('content')
<div class="ed-page-head">
    <div>
        <h1 class="mb-1" style="font-size:clamp(1.8rem,3vw,2.4rem)">{{ $course->translation()?->title }}</h1>
        <p class="text-muted mb-0">{{ __('Continue where you left off and track your progress.') }}</p>
    </div>
</div>

<div class="learn-shell">
    <aside class="learn-sidebar">
        @foreach($course->sections as $section)
            <div class="mb-3">
                <div class="small text-uppercase text-muted fw-bold mb-2">{{ $section->title }}</div>
                @foreach($section->lessons as $lesson)
                    @php($done = $progress->get($lesson->id)?->is_completed)
                    <a href="{{ route('learn.lesson', [$course, $lesson]) }}" class="learn-lesson-link">
                        <span>{{ $lesson->title }}</span>
                        @if($done)
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                            <span class="small text-muted">{{ $lesson->type }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </aside>

    <div class="ed-panel p-4 p-lg-5">
        <h2 class="h4 mb-2">{{ __('Ready to learn?') }}</h2>
        <p class="text-muted mb-4">{{ __('Select a lesson from the sidebar to begin watching, reading, or taking a quiz.') }}</p>
        @php($firstLesson = $course->sections->first()?->lessons->first())
        @if($firstLesson)
            <a href="{{ route('learn.lesson', [$course, $firstLesson]) }}" class="btn btn-primary">{{ __('Start first lesson') }}</a>
        @endif
    </div>
</div>
@endsection
