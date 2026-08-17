@extends('layouts.panel')
@section('heading', __('Exams'))
@section('sidebar')
@include('student.partials.nav')
@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Exams') }}</h2>
        <p>{{ __('Exams available for the courses you are enrolled in.') }}</p>
    </div>
</div>

<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>{{ __('Exam title') }}</th>
                    <th>{{ __('Course') }}</th>
                    <th>{{ __('Questions') }}</th>
                    <th>{{ __('Duration (minutes)') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>

                </tr>
            </thead>
            <tbody>
                @forelse($exams as $exam)
                    <tr>
                        <td class="fw-semibold">{{ $exam->title }}</td>
                        <td>{{ $exam->course->translation()?->title }}</td>
                        <td>{{ $exam->exam_questions_count }}</td>
                        <td>{{ $exam->duration_minutes ?? __('No limit') }}</td>
                        <td>
                            @if (! $exam->attempt)
                                <span class="badge text-bg-light">{{ __('Not started') }}</span>
                            @elseif ($exam->attempt->status === 'in_progress')
                                <span class="badge text-bg-warning">{{ __('In progress') }}</span>
                            @elseif ($exam->attempt->status === 'submitted')
                                <span class="badge text-bg-secondary">{{ __('Pending review') }}</span>
                            @elseif ($exam->attempt->passed)
                                <span class="badge text-bg-success">{{ __('Passed') }}</span>
                            @else
                                <span class="badge text-bg-danger">{{ __('Failed') }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            @if (! $exam->attempt)
                                <a href="{{ route('exams.show', $exam) }}" class="btn btn-sm btn-primary">{{ __('Start') }}</a>
                            @elseif ($exam->attempt->status === 'in_progress')
                                <a href="{{ route('exams.attempt', $exam) }}" class="btn btn-sm btn-primary">{{ __('Continue') }}</a>
                            @else
                                <a href="{{ route('exams.result', $exam) }}" class="btn btn-sm btn-outline-primary">{{ __('View result') }}</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-4 text-muted">{{ __('No exams available yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
