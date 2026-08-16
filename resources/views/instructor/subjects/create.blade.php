@extends('layouts.panel')
@section('heading', __('Add subject'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Add subject') }}</h2>
    </div>
    <a href="{{ route('instructor.subjects.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> {{ __('Back to subjects') }}
    </a>
</div>

<div class="ed-panel p-4">
    <form method="POST" action="{{ route('instructor.subjects.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
            <label class="form-label">{{ __('Course') }}</label>
            <select name="course_id" class="form-select" required>
                <option value="">{{ __('Select a course') }}</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected((string) $selectedCourseId === (string) $course->id)>
                        {{ $course->translation()?->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Subject name') }}</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">{{ __('Add subject') }}</button>
        </div>
    </form>
</div>
@endsection
