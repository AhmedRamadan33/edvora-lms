@extends('layouts.panel')
@section('heading', __('Edit exam'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Edit exam') }}</h2>
    </div>
    <a href="{{ route('instructor.exams.show', $exam) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> {{ __('Back to exam') }}
    </a>
</div>

<div class="ed-panel p-4">
    <form method="POST" action="{{ route('instructor.exams.update', $exam) }}" class="row g-3">
        @csrf
        @method('PUT')
        <div class="col-md-8">
            <label class="form-label">{{ __('Exam title') }}</label>
            <input type="text" name="title" class="form-control" required value="{{ old('title', $exam->title) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('Pass percent') }}</label>
            <input type="number" name="pass_percent" class="form-control" min="1" max="100" required value="{{ old('pass_percent', $exam->pass_percent) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('Duration (minutes)') }}</label>
            <input type="number" name="duration_minutes" class="form-control" min="1" max="600" value="{{ old('duration_minutes', $exam->duration_minutes) }}">
            <div class="form-text">{{ __('Leave empty for no time limit.') }}</div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">{{ __('Save changes') }}</button>
        </div>
    </form>
</div>
@endsection
