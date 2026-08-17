@extends('layouts.app')
@section('title', $exam->title)
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="ed-panel p-4 p-md-5 text-center">
            <span class="badge text-bg-light mb-3">{{ $exam->course->translation()?->title }}</span>
            <h1 class="h3 mb-3">{{ $exam->title }}</h1>

            <div class="row g-3 my-4 text-center">
                <div class="col-4">
                    <div class="h4 mb-0">{{ $questionCount }}</div>
                    <div class="text-muted small">{{ __('Questions') }}</div>
                </div>
                <div class="col-4">
                    <div class="h4 mb-0">{{ $exam->duration_minutes ?? __('No limit') }}</div>
                    <div class="text-muted small">{{ __('Duration (minutes)') }}</div>
                </div>
                <div class="col-4">
                    <div class="h4 mb-0">{{ $exam->pass_percent }}%</div>
                    <div class="text-muted small">{{ __('Pass percent') }}</div>
                </div>
            </div>

            <ul class="list-unstyled text-start text-muted small mb-4">
                <li class="mb-1"><i class="bi bi-check-circle text-success me-1"></i> {{ __('You get one attempt at this exam - answer carefully.') }}</li>
                @if ($exam->duration_minutes)
                    <li class="mb-1"><i class="bi bi-check-circle text-success me-1"></i> {{ __('The exam will submit automatically when the time runs out.') }}</li>
                @endif
                <li class="mb-1"><i class="bi bi-check-circle text-success me-1"></i> {{ __('You can move between questions freely before submitting.') }}</li>
            </ul>

            <form method="POST" action="{{ route('exams.start', $exam) }}">
                @csrf
                <button class="btn btn-primary btn-lg px-5">{{ __('Start exam') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
