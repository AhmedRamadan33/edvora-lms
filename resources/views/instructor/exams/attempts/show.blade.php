@extends('layouts.panel')
@section('heading', __('Grade attempt'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ $exam->title }}</h2>
        <p>{{ $attempt->user->name }} · {{ $attempt->user->email }}</p>
    </div>
    <a href="{{ route('instructor.exams.attempts.index', $exam) }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to results') }}</a>
</div>

<div class="ed-panel p-4 p-md-5 text-center mb-4">
    @if ($attempt->isPendingReview())
        <div class="alert alert-warning d-inline-block">
            {{ __('Some answers still need to be graded by the instructor. Your final result will appear here once grading is complete.') }}
        </div>
        <div class="text-muted">{{ __('Auto-graded score so far') }}: {{ $attempt->auto_score }} / {{ $attempt->total_points }}</div>
    @else
        <div class="display-6 mb-2">{{ $attempt->scorePercent() }}%</div>
        @if ($attempt->passed)
            <span class="badge text-bg-success fs-6">{{ __('Passed') }}</span>
        @else
            <span class="badge text-bg-danger fs-6">{{ __('Failed') }}</span>
        @endif
        <div class="text-muted mt-2">{{ $attempt->auto_score }} / {{ $attempt->total_points }} {{ __('points') }} · {{ __('Pass percent') }}: {{ $exam->pass_percent }}%</div>
    @endif

    @if ($attempt->reviewed_at)
        <div class="text-muted small mt-2">
            {{ __('Last reviewed by :name on :date', ['name' => $attempt->reviewer?->name ?? '—', 'date' => $attempt->reviewed_at->format('Y-m-d H:i')]) }}
        </div>
    @endif
</div>

@php
    $hasManuallyGraded = $attempt->answers->contains(
        fn ($answer) => in_array($answer->bankQuestion->type, \App\Models\BankQuestion::MANUALLY_GRADED_TYPES, true)
    );
@endphp

<form method="POST" action="{{ route('instructor.exams.attempts.grade', [$exam, $attempt]) }}">
    @csrf
    <div class="ed-panel p-4">
        <h2 class="h5 mb-3">{{ __('Answers') }}</h2>

        <div class="list-group">
            @foreach ($attempt->answers as $answer)
                @php $question = $answer->bankQuestion; @endphp
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                        <div>{{ $loop->iteration }}. {{ $question->question }}</div>
                        <div class="flex-shrink-0">
                            <span class="badge text-bg-light">{{ \App\Models\BankQuestion::typeLabel($question->type) }}</span>
                            @if ($answer->is_correct === null)
                                <span class="badge text-bg-secondary">{{ __('Pending review') }}</span>
                            @elseif ($answer->is_correct)
                                <span class="badge text-bg-success">{{ __(':points / :total pts', ['points' => $answer->points_awarded, 'total' => $question->points]) }}</span>
                            @else
                                <span class="badge text-bg-danger">{{ __(':points / :total pts', ['points' => $answer->points_awarded, 'total' => $question->points]) }}</span>
                            @endif
                        </div>
                    </div>

                    @if (in_array($question->type, ['mcq_single', 'true_false'], true))
                        <ul class="list-group">
                            @foreach ($question->choices as $choice)
                                @php $wasSelected = (int) ($answer->answer_data['choice_id'] ?? 0) === $choice->id; @endphp
                                <li class="list-group-item {{ $choice->is_correct ? 'list-group-item-success' : ($wasSelected ? 'list-group-item-danger' : '') }}">
                                    {{ $choice->text }}
                                    @if ($choice->is_correct) <i class="bi bi-check-circle-fill text-success"></i> @endif
                                    @if ($wasSelected && ! $choice->is_correct) <i class="bi bi-x-circle-fill text-danger"></i> @endif
                                    @if ($wasSelected) <span class="badge text-bg-light ms-1">{{ __('Student answer') }}</span> @endif
                                </li>
                            @endforeach
                        </ul>
                    @elseif ($question->type === 'matching')
                        @php $submittedPairs = $answer->answer_data['pairs'] ?? []; @endphp
                        <table class="table table-bordered mb-0">
                            <thead><tr><th>{{ __('Key') }}</th><th>{{ __('Student answer') }}</th><th>{{ __('Correct value') }}</th></tr></thead>
                            <tbody>
                                @foreach ($question->matches as $match)
                                    @php $selected = $submittedPairs[$match->id] ?? null; @endphp
                                    <tr>
                                        <td>{{ $match->prompt_text }}</td>
                                        <td class="{{ $selected === $match->match_text ? 'text-success' : 'text-danger' }}">{{ $selected ?: '—' }}</td>
                                        <td>{{ $match->match_text }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="border rounded-3 p-3 bg-light mb-3">
                            {{ $answer->answer_data['text'] ?? __('No answer submitted.') }}
                        </div>
                        <input type="hidden" name="answers[{{ $loop->index }}][bank_question_id]" value="{{ $question->id }}">
                        <div class="row g-3">
                            <div class="col-sm-3">
                                <label class="form-label">{{ __('Points awarded') }}</label>
                                <input type="number" class="form-control" min="0" max="{{ $question->points }}"
                                    name="answers[{{ $loop->index }}][points_awarded]"
                                    value="{{ old('answers.' . $loop->index . '.points_awarded', $answer->points_awarded) }}">
                            </div>
                            <div class="col-sm-9">
                                <label class="form-label">{{ __('Instructor feedback') }}</label>
                                <textarea class="form-control" rows="2"
                                    name="answers[{{ $loop->index }}][instructor_feedback]">{{ old('answers.' . $loop->index . '.instructor_feedback', $answer->instructor_feedback) }}</textarea>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($hasManuallyGraded)
            <div class="mt-4 text-end">
                <button class="btn btn-primary">{{ __('Save grading') }}</button>
            </div>
        @endif
    </div>
</form>
@endsection
