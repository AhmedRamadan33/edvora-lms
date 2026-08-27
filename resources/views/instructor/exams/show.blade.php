@extends('layouts.panel')
@section('heading', __('Exam details'))
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
        $subjects = $exam->course->subjects;
    @endphp

    <div class="ed-page-head">
        <div>
            <h2>{{ $exam->title }}</h2>
            <p class="text-muted mb-0">{{ $exam->course->translation()?->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('instructor.exams.edit', $exam) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil"></i> {{ __('Edit') }}
            </a>
            <a href="{{ route('instructor.exams.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> {{ __('Back to exams') }}
            </a>
        </div>
    </div>

    <div class="ed-panel p-4 mb-4">
        <div class="d-flex flex-wrap gap-4">
            <div class="text-center">
                <div class="h4 mb-0">{{ $examQuestions->total() }}</div>
                <div class="text-muted small">{{ __('Questions') }}</div>
            </div>
            <div class="text-center">
                <div class="h4 mb-0">{{ $exam->duration_minutes ?? __('No limit') }}</div>
                <div class="text-muted small">{{ __('Duration (minutes)') }}</div>
            </div>
            <div class="text-center">
                <div class="h4 mb-0">{{ $exam->pass_percent }}%</div>
                <div class="text-muted small">{{ __('Pass percent') }}</div>
            </div>
            <div class="text-center">
                <span class="badge text-bg-{{ $exam->status === 'published' ? 'success' : 'warning' }}">
                    {{ $exam->status === 'published' ? __('Published') : __('Draft') }}
                </span>
            </div>
        </div>
    </div>

    @if ($subjects->isNotEmpty())
        <div class="ed-panel p-4 mb-4">
            <h2 class="h5 mb-3">{{ __('Add more questions') }}</h2>

            <form method="POST" action="{{ route('instructor.exams.questions.store', $exam) }}" data-exam-form>
                @csrf

                <div class="mb-3 d-flex flex-wrap gap-3" data-subject-toggles>
                    @foreach ($subjects as $subject)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" data-subject-toggle
                                data-subject-target="add-subject-panel-{{ $subject->id }}" id="add-subject-check-{{ $subject->id }}">
                            <label class="form-check-label" for="add-subject-check-{{ $subject->id }}">{{ $subject->name }}</label>
                        </div>
                    @endforeach
                </div>

                <div data-subject-panels>
                    @foreach ($subjects as $subject)
                        <div class="card border-primary-subtle mb-3 d-none" id="add-subject-panel-{{ $subject->id }}" data-subject-panel>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>{{ $subject->name }}</strong>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-add-type-row>
                                        <i class="bi bi-plus-lg"></i> {{ __('Add question type') }}
                                    </button>
                                </div>
                                <div data-type-rows-list>
                                    <div class="row g-2 align-items-end mb-2" data-type-row>
                                        <input type="hidden" data-rule-subject value="{{ $subject->id }}">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Question type') }}</label>
                                            <select class="form-select" data-rule-type>
                                                @foreach ($typeLabels as $type => $label)
                                                    <option value="{{ $type }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">{{ __('Count') }}</label>
                                            <input type="number" class="form-control" data-rule-count min="1" max="200" value="1">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger" data-remove-type-row>&times;</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button class="btn btn-primary btn-sm">{{ __('Add questions') }}</button>
            </form>

            <template id="exam-type-row-template">
                <div class="row g-2 align-items-end mb-2" data-type-row>
                    <input type="hidden" data-rule-subject value="">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Question type') }}</label>
                        <select class="form-select" data-rule-type>
                            @foreach ($typeLabels as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Count') }}</label>
                        <input type="number" class="form-control" data-rule-count min="1" max="200" value="1">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger" data-remove-type-row>&times;</button>
                    </div>
                </div>
            </template>
        </div>
    @endif

    <div class="ed-panel p-4">
        <h2 class="h5 mb-3">{{ __('Questions') }} ({{ $examQuestions->total() }})</h2>

        <div class="list-group mb-3">
            @forelse ($examQuestions as $examQuestion)
                @php $question = $examQuestion->bankQuestion; @endphp
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                        <div>{{ $examQuestions->firstItem() + $loop->index }}. {{ $question->question }}</div>
                        <div class="flex-shrink-0 d-flex align-items-center gap-2">
                            <span class="badge text-bg-light">{{ $typeLabels[$question->type] ?? $question->type }}</span>
                            <span class="badge text-bg-light">{{ __(':points pts', ['points' => $question->points]) }}</span>
                            @if ($question->subject)
                                <span class="badge text-bg-light">{{ $question->subject->name }}</span>
                            @endif
                            <form method="POST" action="{{ route('instructor.exams.questions.destroy', [$exam, $examQuestion]) }}" data-confirm-delete>
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('Remove') }}</button>
                            </form>
                        </div>
                    </div>

                    @if (in_array($question->type, ['mcq_single', 'true_false'], true))
                        <ul class="list-group">
                            @foreach ($question->choices as $choice)
                                <li class="list-group-item {{ $choice->is_correct ? 'list-group-item-success' : '' }}">
                                    {{ $choice->text }}
                                    @if ($choice->is_correct)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @elseif ($question->type === 'matching')
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead><tr><th>{{ __('Key') }}</th><th>{{ __('Value') }}</th></tr></thead>
                                <tbody>
                                    @foreach ($question->matches as $match)
                                        <tr><td>{{ $match->prompt_text }}</td><td>{{ $match->match_text }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted small">{{ __('Graded manually by the instructor.') }}</div>
                    @endif
                </div>
            @empty
                <div class="list-group-item text-muted">{{ __('No questions were matched for this exam.') }}</div>
            @endforelse
        </div>

        <x-table-pagination :paginator="$examQuestions" />
    </div>
@endsection
