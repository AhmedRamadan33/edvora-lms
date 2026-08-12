@extends('layouts.app')
@section('title', $user->name)
@section('content')
<section class="ed-panel p-4 p-lg-5 mb-4">
    <div class="row align-items-center g-3">
        <div class="col-lg-8">
            <div class="small text-uppercase text-muted fw-bold mb-2">{{ __('Instructor') }}</div>
            <h1 class="mb-2" style="font-size:clamp(2rem,4vw,2.8rem)">{{ $user->name }}</h1>
            <p class="lead text-muted mb-2">{{ $user->instructorProfile?->headline }}</p>
            <p class="mb-0">{{ $user->instructorProfile?->about }}</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <div class="display-font fs-2">{{ $courses->count() }}</div>
            <div class="text-muted">{{ __('Published courses') }}</div>
        </div>
    </div>
</section>

<div class="ed-section__head mb-3">
    <div>
        <h2>{{ __('Courses by :name', ['name' => $user->name]) }}</h2>
    </div>
</div>

<div class="row g-4">
    @forelse($courses as $course)
        <div class="col-md-6 col-xl-3">@include('partials.course-card', ['course' => $course])</div>
    @empty
        <div class="col-12"><div class="alert alert-light border">{{ __('No published courses yet.') }}</div></div>
    @endforelse
</div>
@endsection
