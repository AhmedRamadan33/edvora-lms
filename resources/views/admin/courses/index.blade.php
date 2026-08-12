@extends('layouts.panel')
@section('heading', __('Course review'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Course review') }}</h2>
        <p>{{ __('Approve, reject, or inspect submitted marketplace courses.') }}</p>
    </div>
</div>

<x-table-toolbar :placeholder="__('Search courses or instructors')">
    <select name="status" class="form-select w-auto">
        <option value="">{{ __('All statuses') }}</option>
        @foreach (['pending_review', 'published', 'rejected'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ __status($status) }}</option>
        @endforeach
    </select>
    <select name="category" class="form-select w-auto">
        <option value="">{{ __('All categories') }}</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>
                {{ $category->translation()?->name }}
            </option>
        @endforeach
    </select>
</x-table-toolbar>

<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Course') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Instructor') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courses as $course)
                    <tr>
                        <td>{{ $courses->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $course->translation()?->title }}</div>
                            <div class="small text-muted">{{ money($course->price) }}</div>
                        </td>
                        <td>{{ $course->category?->translation()?->name ?? '—' }}</td>
                        <td>{{ $course->instructor->name }}</td>
                        <td><span class="ed-status is-{{ $course->status }}">{{ __status($course->status) }}</span></td>
                        <td class="text-end pe-3">
                            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-sm btn-outline-primary">{{ __('Review') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$courses" />
@endsection
