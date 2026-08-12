@extends('layouts.panel')
@section('heading', __('My courses'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('My courses') }}</h2>
        <p>{{ __('Create, refine, and submit courses for marketplace review.') }}</p>
    </div>
    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary btn-sm">{{ __('Create') }}</a>
</div>

<x-table-toolbar :placeholder="__('Search my courses')" />
<div class="row g-4">
    @forelse($courses as $course)
        <div class="col-md-6 col-xl-4">
            <div class="ed-panel h-100 p-3 d-flex flex-column">
                <div class="mb-2">
                    <span class="ed-status is-{{ $course->status }}">{{ __status($course->status) }}</span>
                </div>
                <h3 class="h5 mb-2">{{ $course->translation()?->title }}</h3>
                <p class="text-muted small flex-grow-1">{{ Str::limit($course->translation()?->subtitle, 80) }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <strong>{{ number_format($course->price, 2) }} {{ $course->currency }}</strong>
                    <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-sm btn-outline-primary">{{ __('Manage') }}</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="ed-panel p-5 text-center">
                <h3 class="h5">{{ __('No courses yet.') }}</h3>
                <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary mt-2">{{ __('Create course') }}</a>
            </div>
        </div>
    @endforelse
</div>
<x-table-pagination :paginator="$courses" />
@endsection
