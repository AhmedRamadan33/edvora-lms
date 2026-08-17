@extends('layouts.panel')
@section('heading', __('Exams'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Exams') }}</h2>
        <p>{{ __('Exams generated from your question bank.') }}</p>
    </div>
    <a href="{{ route('instructor.exams.create') }}" class="btn btn-primary btn-sm">{{ __('Create exam') }}</a>
</div>

<x-table-toolbar :placeholder="__('Search exams')">
    <select name="course_id" class="form-select w-auto">
        <option value="">{{ __('All courses') }}</option>
        @foreach ($courses as $course)
            <option value="{{ $course->id }}" @selected(request('course_id') == $course->id)>{{ $course->translation()?->title }}</option>
        @endforeach
    </select>
    <select name="status" class="form-select w-auto">
        <option value="">{{ __('All statuses') }}</option>
        <option value="draft" @selected(request('status') === 'draft')>{{ __('Draft') }}</option>
        <option value="published" @selected(request('status') === 'published')>{{ __('Published') }}</option>
    </select>
</x-table-toolbar>

<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Course') }}</th>
                    <th>{{ __('Questions') }}</th>
                    <th>{{ __('Duration (minutes)') }}</th>
                    <th>{{ __('Pass percent') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exams as $exam)
                    <tr>
                        <td>{{ $exams->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold">
                            <a href="{{ route('instructor.exams.show', $exam) }}">{{ $exam->title }}</a>
                        </td>
                        <td>{{ $exam->course->translation()?->title }}</td>
                        <td>{{ $exam->exam_questions_count }}</td>
                        <td>{{ $exam->duration_minutes ?? __('No limit') }}</td>
                        <td>{{ $exam->pass_percent }}%</td>
                        <td>
                            <span class="badge text-bg-{{ $exam->status === 'published' ? 'success' : 'warning' }}">
                                {{ $exam->status === 'published' ? __('Published') : __('Draft') }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('instructor.exams.edit', $exam) }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('instructor.exams.status', $exam) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary">
                                        {{ $exam->status === 'published' ? __('Unpublish') : __('Publish') }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('instructor.exams.destroy', $exam) }}" data-confirm-delete>
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-muted p-4">{{ __('No exams yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$exams" />
@endsection
