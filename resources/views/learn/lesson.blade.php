@extends('layouts.app')
@section('title', $lesson->title)
@section('content')
    @php
        $isCompleted = $progress->get($lesson->id)?->is_completed;
        $typeLabels = [
            'video' => __('Video'),
            'article' => __('Article'),
            'file' => __('File'),
            'quiz' => __('Quiz'),
        ];
    @endphp

<div class="mb-3">
    <a href="{{ route('learn.course', $course) }}" class="small fw-semibold">← {{ $course->translation()?->title }}</a>
    <div class="d-flex align-items-center gap-2 mt-2 mb-1">
        <h1 class="mb-0" style="font-size:clamp(1.6rem,3vw,2.2rem)">{{ $lesson->title }}</h1>
        @if($isCompleted)
            <span class="ed-status is-approved">{{ __('Completed') }}</span>
        @endif
    </div>
    <div class="text-muted">{{ $typeLabels[$lesson->type] ?? ucfirst($lesson->type) }} {{ __('lesson') }}</div>
</div>

<div class="learn-shell">
    @include('learn.partials.sidebar', ['activeLesson' => $lesson])

    <div>
        <div class="row g-4">
            <div class="col-lg-8">
                @if($lesson->type === 'video')
                    <div class="secure-player mb-3" oncontextmenu="return false;">
                        @if($otp && $playbackInfo)
                            <iframe src="https://player.vdocipher.com/v2/?otp={{ $otp }}&playbackInfo={{ $playbackInfo }}" allow="encrypted-media" allowfullscreen loading="lazy"></iframe>
                        @else
                            <div class="text-white p-5 text-center">{{ __('Video is processing or unavailable.') }}</div>
                        @endif
                    </div>
                @elseif($lesson->type === 'article')
                    <div class="ed-panel p-4 mb-3">
                        {!! $lesson->content !!}
                    </div>
                @elseif($lesson->type === 'file' && $lesson->attachment)
                    <div class="ed-panel p-4 mb-3">
                        <p class="mb-3">{{ __('Download the lesson attachment to continue.') }}</p>
                        <a class="btn btn-outline-primary" href="{{ asset('storage/'.$lesson->attachment) }}">{{ __('Download file') }}</a>
                    </div>
                @elseif($lesson->type === 'quiz' && $lesson->quiz)
                    @if($quizAttempt)
                        <div class="ed-panel p-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h4 mb-0">{{ $lesson->quiz->title }}</h2>
                                <span class="badge text-bg-{{ $quizAttempt->passed ? 'success' : 'danger' }} fs-6">
                                    {{ $quizAttempt->passed ? __('Passed') : __('Failed') }}
                                </span>
                            </div>
                            <p class="text-muted mb-4">{{ __('Your score') }}: {{ $quizAttempt->score }}% ({{ __('Pass percent') }}: {{ $lesson->quiz->pass_percent }}%)</p>

                            <div class="list-group">
                                @foreach($lesson->quiz->questions as $question)
                                    @php
                                        $selectedIndex = $quizAttempt->answers[$question->id] ?? null;
                                        $isCorrect = $selectedIndex !== null && (int) $selectedIndex === (int) $question->correct_index;
                                    @endphp
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                            <div>{{ $loop->iteration }}. {{ $question->question }}</div>
                                            <span class="badge text-bg-{{ $isCorrect ? 'success' : 'danger' }} flex-shrink-0">
                                                {{ $isCorrect ? __('Correct') : __('Incorrect') }}
                                            </span>
                                        </div>
                                        <ul class="list-group">
                                            @foreach($question->options as $index => $option)
                                                @php
                                                    $isRight = $index === (int) $question->correct_index;
                                                    $wasSelected = $selectedIndex !== null && (int) $selectedIndex === $index;
                                                @endphp
                                                <li class="list-group-item {{ $isRight ? 'list-group-item-success' : ($wasSelected ? 'list-group-item-danger' : '') }}">
                                                    {{ $option }}
                                                    @if($isRight) <i class="bi bi-check-circle-fill text-success"></i> @endif
                                                    @if($wasSelected && ! $isRight) <i class="bi bi-x-circle-fill text-danger"></i> @endif
                                                    @if($wasSelected) <span class="badge text-bg-light ms-1">{{ __('Your answer') }}</span> @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('learn.quiz', [$course, $lesson]) }}" class="ed-panel p-4 mb-3">
                            @csrf
                            <h2 class="h4 mb-1">{{ $lesson->quiz->title }}</h2>
                            <p class="text-muted mb-4">{{ __('Pass percent') }}: {{ $lesson->quiz->pass_percent }}%</p>
                            @foreach($lesson->quiz->questions as $question)
                                <div class="mb-4">
                                    <div class="fw-semibold mb-2">{{ $loop->iteration }}. {{ $question->question }}</div>
                                    @foreach($question->options as $index => $option)
                                        <label class="quiz-option">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $index }}" class="form-check-input me-2" required>
                                            {{ $option }}
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                            <button class="btn btn-primary">{{ __('Submit quiz') }}</button>
                        </form>
                    @endif
                @endif

                @if($isCompleted)
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-check-circle-fill"></i> {{ __('You have completed this lesson.') }}
                    </div>
                @else
                    <form method="POST" action="{{ route('learn.complete', [$course, $lesson]) }}" class="mb-3">
                        @csrf
                        <button class="btn btn-success">{{ __('Mark as complete') }}</button>
                    </form>
                @endif

                <div class="d-flex justify-content-between align-items-center mt-4">
                    @if($previousLesson)
                        <a href="{{ route('learn.lesson', [$course, $previousLesson]) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>{{ __('Previous') }}
                        </a>
                    @else
                        <span></span>
                    @endif
                    @if($nextLesson)
                        <a href="{{ route('learn.lesson', [$course, $nextLesson]) }}" class="btn btn-outline-primary">
                            {{ __('Next lesson') }}<i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ed-panel p-4 mb-3">
                    <h2 class="h6 mb-3">{{ __('Ask a question') }}</h2>
                    <form method="POST" action="{{ route('learn.ask', [$course, $lesson]) }}">
                        @csrf
                        <input name="title" class="form-control mb-2" placeholder="{{ __('Question title') }}" required>
                        <textarea name="body" class="form-control mb-2" rows="3" placeholder="{{ __('Describe your question') }}" required></textarea>
                        <button class="btn btn-outline-primary btn-sm">{{ __('Post') }}</button>
                    </form>
                </div>

                @foreach($questions as $question)
                    <div class="ed-panel p-3 mb-2">
                        <strong>{{ $question->title }}</strong>
                        <div class="small text-muted mb-2">{{ $question->user->name }}</div>
                        <p class="mb-3">{{ $question->body }}</p>
                        @foreach($question->answers as $answer)
                            <div class="border-start ps-2 mb-2 small">
                                <strong>{{ $answer->user->name }}:</strong> {{ $answer->body }}
                            </div>
                        @endforeach
                        <form method="POST" action="{{ route('learn.answer', [$course, $question]) }}">
                            @csrf
                            <input name="body" class="form-control form-control-sm mb-1" placeholder="{{ __('Write a reply') }}" required>
                            <button class="btn btn-sm btn-outline-primary">{{ __('Reply') }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
