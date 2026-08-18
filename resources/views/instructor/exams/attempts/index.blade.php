@extends('layouts.panel')
@section('heading', __('Exam results'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ $exam->title }}</h2>
        <p>{{ $exam->course->translation()?->title }}</p>
    </div>
    <a href="{{ route('instructor.exams.show', $exam) }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to exam') }}</a>
</div>

<x-table-toolbar :placeholder="__('Search by student name or email')">
    <select name="status" class="form-select w-auto">
        <option value="">{{ __('All statuses') }}</option>
        <option value="in_progress" @selected(request('status') === 'in_progress')>{{ __('In progress') }}</option>
        <option value="submitted" @selected(request('status') === 'submitted')>{{ __('Pending review') }}</option>
        <option value="graded" @selected(request('status') === 'graded')>{{ __('Graded') }}</option>
    </select>
</x-table-toolbar>

<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Student') }}</th>
                    <th>{{ __('Submitted at') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Score') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts as $attempt)
                    <tr>
                        <td>{{ $attempts->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $attempt->user->name }}</div>
                            <div class="text-muted small">{{ $attempt->user->email }}</div>
                        </td>
                        <td>{{ $attempt->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>
                            <span class="badge text-bg-{{ $attempt->status === 'graded' ? 'success' : ($attempt->status === 'submitted' ? 'warning' : 'secondary') }}">
                                {{ $attempt->status === 'graded' ? __('Graded') : ($attempt->status === 'submitted' ? __('Pending review') : __('In progress')) }}
                            </span>
                            @if ($attempt->passed !== null)
                                <span class="badge text-bg-{{ $attempt->passed ? 'success' : 'danger' }}">
                                    {{ $attempt->passed ? __('Passed') : __('Failed') }}
                                </span>
                            @endif
                        </td>
                        <td>{{ $attempt->status === 'in_progress' ? '—' : "{$attempt->auto_score} / {$attempt->total_points}" }}</td>
                        <td class="text-end">
                            <a href="{{ route('instructor.exams.attempts.show', [$exam, $attempt]) }}" class="btn btn-sm btn-outline-primary">
                                {{ $attempt->status === 'submitted' ? __('Grade') : __('Review') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted p-4">{{ __('No attempts yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$attempts" />
@endsection
