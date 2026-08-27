@extends('layouts.panel')
@section('heading', __('Question bank'))
@section('sidebar')@include('instructor.partials.nav')@endsection
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

    <div class="ed-page-head">
        <div>
            <h2>{{ __('Question bank') }} · {{ $course->translation()?->title }}</h2>
            <p class="text-muted mb-0">{{ __('Build a reusable pool of questions for this course.') }}</p>
        </div>
        <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> {{ __('Back to course') }}
        </a>
    </div>

    <div class="ed-panel p-4 mb-4">
        <div class="d-flex flex-wrap gap-4">
            <div class="text-center">
                <div class="h4 mb-0">{{ array_sum($stats) }}</div>
                <div class="text-muted small">{{ __('Total questions') }}</div>
            </div>
            @foreach ($typeLabels as $type => $label)
                <div class="text-center">
                    <div class="h4 mb-0">{{ $stats[$type] ?? 0 }}</div>
                    <div class="text-muted small">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="ed-panel p-4 mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">{{ __('Search') }}</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('Type') }}</label>
                <select name="type" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($typeLabels as $type => $label)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('Difficulty') }}</label>
                <select name="difficulty" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach (['easy', 'medium', 'hard'] as $level)
                        <option value="{{ $level }}" @selected(request('difficulty') === $level)>{{ __($level) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('Subject') }}</label>
                <select name="subject_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 form-check ms-2">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="filter-active"
                    @checked(request('is_active') == '1')>
                <label class="form-check-label" for="filter-active">{{ __('Active only') }}</label>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary btn-sm">{{ __('Filter') }}</button>
                @if (request()->anyFilled(['search', 'type', 'difficulty', 'subject_id', 'is_active']))
                    <a href="{{ route('instructor.question-bank.index', $course) }}" class="btn btn-outline-secondary btn-sm">{{ __('Reset') }}</a>
                @endif
            </div>
        </form>
    </div>

    <div class="ed-panel p-4 mb-4">
        <h2 class="h5 mb-3">{{ __('Add question') }}</h2>
        @include('instructor.courses.question-bank._form', ['course' => $course, 'question' => null, 'formId' => 'new'])
    </div>

    <div class="ed-panel p-4 mb-4">
        <h2 class="h5 mb-3">{{ __('Questions') }} ({{ $items->total() }})</h2>

        <div class="list-group">
            @forelse ($items as $question)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div>{{ Str::limit($question->question, 120) }}</div>
                            <div class="mt-1">
                                <span class="badge text-bg-light">{{ $typeLabels[$question->type] ?? $question->type }}</span>
                                <span class="badge text-bg-light">{{ __($question->difficulty) }}</span>
                                <span class="badge text-bg-light">{{ __(':points pts', ['points' => $question->points]) }}</span>
                                @if ($question->subject)
                                    <span class="badge text-bg-light">{{ $question->subject->name }}</span>
                                @endif
                                @if (! $question->is_active)
                                    <span class="badge text-bg-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse"
                                data-bs-target="#edit-question-{{ $question->id }}">
                                {{ __('Edit') }}
                            </button>
                            <form method="POST" action="{{ route('instructor.question-bank.toggle', [$course, $question]) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary">
                                    {{ $question->is_active ? __('Deactivate') : __('Activate') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('instructor.question-bank.destroy', [$course, $question]) }}"
                                data-confirm-delete>
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>

                    <div class="collapse mt-3" id="edit-question-{{ $question->id }}">
                        @include('instructor.courses.question-bank._form', [
                            'course' => $course,
                            'question' => $question,
                            'formId' => $question->id,
                        ])
                    </div>
                </div>
            @empty
                <div class="list-group-item text-muted">{{ __('No questions yet. Add your first question above.') }}</div>
            @endforelse
        </div>

        <x-table-pagination :paginator="$items" />
    </div>

    <template id="bank-choice-row-template">
        <div class="row g-2 align-items-center mb-2" data-choice-row>
            <div class="col-auto">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" data-choice-correct value="1">
                    <label class="form-check-label">{{ __('Correct') }}</label>
                </div>
            </div>
            <div class="col">
                <input type="text" class="form-control" data-choice-text placeholder="{{ __('Option text') }}">
            </div>
            <div class="col-auto">
                <input type="file" class="form-control" data-choice-image accept="image/*">
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-danger btn-sm" data-remove-choice>&times;</button>
            </div>
        </div>
    </template>

    <template id="bank-match-row-template">
        <div class="row g-2 mb-2" data-match-row>
            <div class="col-12 col-sm-5">
                <input class="form-control" data-match-prompt placeholder="{{ __('Key') }}">
            </div>
            <div class="col-12 col-sm-5">
                <input class="form-control" data-match-answer placeholder="{{ __('Value') }}">
            </div>
            <div class="col-12 col-sm-2">
                <button type="button" class="btn btn-outline-danger w-100" data-remove-match>&times;</button>
            </div>
        </div>
    </template>
@endsection
