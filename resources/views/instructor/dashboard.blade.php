@extends('layouts.panel')
@section('heading', __('Instructor Dashboard'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Instructor overview') }}</h2>
        <p>{{ __('Track your courses, students, and earnings.') }}</p>
    </div>
    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary btn-sm">{{ __('Create course') }}</a>
</div>

<div class="row g-3 mb-4">
    @foreach($stats as $label => $value)
        <div class="col-md-4 col-xl">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ __(str_replace('_', ' ', ucfirst($label))) }}</div>
                    <div class="fs-4 fw-bold mt-1">{{ number_format($value, (floor($value) != $value ? 2 : 0)) }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="ed-panel p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h5 mb-0">{{ __('Recent courses') }}</h3>
        <a href="{{ route('instructor.courses.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
    </div>
    <ul class="list-group list-group-flush">
        @forelse($courses as $course)
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span>
                    {{ $course->translation()?->title }}
                    <span class="ed-status is-{{ $course->status }} ms-2">{{ __status($course->status) }}</span>
                </span>
                <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-sm btn-outline-primary">{{ __('Manage') }}</a>
            </li>
        @empty
            <li class="list-group-item px-0 text-muted">{{ __('No courses yet.') }}</li>
        @endforelse
    </ul>
</div>
@endsection
