@extends('layouts.panel')
@section('heading', __('Subjects'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Subjects') }}</h2>
        <p>{{ __('Organize your question bank by subject, per course.') }}</p>
    </div>
    <a href="{{ route('instructor.subjects.create') }}" class="btn btn-primary btn-sm">{{ __('Add subject') }}</a>
</div>

<x-table-toolbar :placeholder="__('Search subjects')">
    <select name="course_id" class="form-select w-auto">
        <option value="">{{ __('All courses') }}</option>
        @foreach ($courses as $course)
            <option value="{{ $course->id }}" @selected(request('course_id') == $course->id)>{{ $course->translation()?->title }}</option>
        @endforeach
    </select>
</x-table-toolbar>

<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Subject') }}</th>
                    <th>{{ __('Course') }}</th>
                    <th>{{ __('Questions') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $subject)
                    <tr>
                        <td>{{ $subjects->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold">{{ $subject->name }}</td>
                        <td>{{ $subject->course->translation()?->title }}</td>
                        <td>{{ $subject->bank_questions_count }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('instructor.subjects.destroy', $subject) }}" data-confirm-delete>
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted p-4">{{ __('No subjects yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$subjects" />
@endsection
