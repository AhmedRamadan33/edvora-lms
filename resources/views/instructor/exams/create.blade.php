@extends('layouts.panel')
@section('heading', __('Create exam'))
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
            <h2>{{ __('Create exam') }}</h2>
            <p class="text-muted mb-0">{{ __('Pick a course and subjects, then choose how many questions of each type to pull from the bank.') }}</p>
        </div>
        <a href="{{ route('instructor.exams.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> {{ __('Back to exams') }}
        </a>
    </div>

    <div class="ed-panel p-4 mb-4">
        <form method="GET" action="{{ route('instructor.exams.create') }}" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">{{ __('Course') }}</label>
                <select name="course_id" class="form-select" onchange="this.form.submit()">
                    <option value="">{{ __('Select a course') }}</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected($selectedCourse && $selectedCourse->id === $course->id)>
                            {{ $course->translation()?->title }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">{{ __('Choosing a course reloads the page with its subjects below.') }}</div>
            </div>
        </form>
    </div>

    @if ($selectedCourse)
        @if ($subjects->isEmpty())
            <div class="ed-panel p-4 mb-4">
                <div class="text-muted">
                    {{ __('This course has no subjects yet.') }}
                    <a href="{{ route('instructor.subjects.create', ['course_id' => $selectedCourse->id]) }}">{{ __('Add one') }}</a>
                </div>
            </div>
        @else
            <div class="ed-panel p-4">
                <form method="POST" action="{{ route('instructor.exams.store') }}" data-exam-form>
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $selectedCourse->id }}">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Exam title') }}</label>
                            <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Duration (minutes)') }}</label>
                            <input type="number" name="duration_minutes" class="form-control" min="1" max="600" value="{{ old('duration_minutes') }}">
                            <div class="form-text">{{ __('Leave empty for no time limit.') }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Pass percent') }}</label>
                            <input type="number" name="pass_percent" class="form-control" min="1" max="100" value="{{ old('pass_percent', 60) }}" required>
                        </div>
                    </div>

                    <strong class="d-block mb-2">{{ __('Subjects') }}</strong>
                    <div class="form-text mb-2">{{ __('Pick one or more subjects, then add one or more question-type rules under each.') }}</div>

                    <div class="mb-3 d-flex flex-wrap gap-3" data-subject-toggles>
                        @foreach ($subjects as $subject)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" data-subject-toggle
                                    data-subject-target="subject-panel-{{ $subject->id }}" id="subject-check-{{ $subject->id }}">
                                <label class="form-check-label" for="subject-check-{{ $subject->id }}">{{ $subject->name }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div data-subject-panels>
                        @foreach ($subjects as $subject)
                            <div class="card border-primary-subtle mb-3 d-none" id="subject-panel-{{ $subject->id }}" data-subject-panel>
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

                    <div class="mt-3">
                        <button class="btn btn-primary">{{ __('Generate exam') }}</button>
                    </div>
                </form>
            </div>

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
        @endif
    @endif
@endsection
