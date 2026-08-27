@extends('layouts.app')
@section('title', $exam->title)
@section('content')
    @php
        $typeLabels = [
            'mcq_single' => __('Multiple choice'),
            'true_false' => __('True / False'),
            'matching' => __('Matching'),
            'fill_blank' => __('Fill in the blank'),
            'essay' => __('Essay'),
        ];
    @endphp

    <form method="POST" action="{{ route('exams.submit', $exam) }}" id="exam-attempt-form" data-exam-attempt
        data-started-at="{{ $attempt->started_at->toIso8601String() }}"
        data-duration-minutes="{{ $exam->duration_minutes ?? '' }}"
        data-confirm-message="{{ __('Submit the exam now? You cannot change your answers afterwards.') }}"
        data-confirm-label="{{ __('Submit exam') }}">
        @csrf

        <div class="ed-panel p-3 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2" style="position:sticky;top:.5rem;z-index:10;">
            <div>
                <div class="fw-semibold">{{ $exam->title }}</div>
                <div class="text-muted small">{{ __('Answered') }}: <span data-progress-count>0</span> / {{ count($questions) }}</div>
            </div>
            @if ($exam->duration_minutes)
                <div class="h5 mb-0" data-exam-timer>--:--</div>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-lg-3 order-lg-2">
                <div class="ed-panel p-3">
                    <div class="text-muted small mb-2">{{ __('Questions') }}</div>
                    <div class="d-flex flex-wrap gap-2" data-question-nav>
                        @foreach ($questions as $index => $question)
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-nav-to="{{ $index }}">{{ $index + 1 }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-9 order-lg-1">
                @foreach ($questions as $index => $question)
                    <div class="ed-panel p-4 mb-3 {{ $index === 0 ? '' : 'd-none' }}" data-question-panel data-index="{{ $index }}">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div class="fw-semibold">{{ $index + 1 }}. {{ $question->question }}</div>
                            <span class="badge text-bg-light flex-shrink-0">{{ $typeLabels[$question->type] ?? $question->type }}</span>
                        </div>

                        @if ($question->image)
                            <img src="{{ asset('storage/'.$question->image) }}" alt="" class="img-fluid rounded mb-3" style="max-height:220px;">
                        @endif

                        @if (in_array($question->type, ['mcq_single', 'true_false'], true))
                            @foreach ($question->choices as $choice)
                                <label class="d-flex align-items-center gap-2 border rounded-3 p-2 mb-2">
                                    <input type="radio" class="form-check-input m-0" name="answers[{{ $question->id }}]"
                                        value="{{ $choice->id }}" data-answer-input data-question-index="{{ $index }}">
                                    <span>{{ $choice->text }}</span>
                                </label>
                            @endforeach
                        @elseif ($question->type === 'matching')
                            @php $options = $question->matches->pluck('match_text')->shuffle()->values(); @endphp
                            @foreach ($question->matches as $match)
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-12 col-sm-5">{{ $match->prompt_text }}</div>
                                    <div class="col-12 col-sm-7">
                                        <select class="form-select" name="answers[{{ $question->id }}][{{ $match->id }}]"
                                            data-answer-input data-question-index="{{ $index }}">
                                            <option value="">{{ __('Select a match') }}</option>
                                            @foreach ($options as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <textarea class="form-control" rows="5" name="answers[{{ $question->id }}]"
                                placeholder="{{ __('Write your answer...') }}" data-answer-input data-question-index="{{ $index }}"></textarea>
                        @endif
                    </div>
                @endforeach

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-nav-prev>{{ __('Previous') }}</button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-nav-next>{{ __('Next') }}</button>
                        <button type="submit" class="btn btn-success d-none" data-exam-submit>{{ __('Submit exam') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
