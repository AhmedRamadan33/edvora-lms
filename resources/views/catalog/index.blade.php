@extends('layouts.app')
@section('title', __('Courses').' - '.\App\Services\SettingService::platformName())
@section('description', __('Explore expert-led courses across the marketplace.'))
@section('content')
<div class="ed-section__head mb-4">
    <div>
        <h1 class="mb-1" style="font-size:clamp(2rem,4vw,2.8rem)">{{ __('Course catalog') }}</h1>
        <p class="text-muted mb-0">{{ __('Explore expert-led courses across the marketplace.') }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-3">
        <form class="filters-panel" method="GET" action="{{ route('courses.index') }}">
            <h2 class="h6 mb-3">{{ __('Filters') }}</h2>
            <label class="form-label">{{ __('Search') }}</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control mb-3" placeholder="{{ __('Search courses') }}">

            <label class="form-label">{{ __('Category') }}</label>
            <select name="category" class="form-select mb-3">
                <option value="">{{ __('All categories') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                        {{ $category->translation()?->name }}
                    </option>
                @endforeach
            </select>

            <label class="form-label">{{ __('Level') }}</label>
            <select name="level" class="form-select mb-4">
                <option value="">{{ __('All levels') }}</option>
                @foreach(['beginner', 'intermediate', 'advanced'] as $level)
                    <option value="{{ $level }}" @selected(request('level') === $level)>{{ ucfirst($level) }}</option>
                @endforeach
            </select>

            <button class="btn btn-primary w-100">{{ __('Apply filters') }}</button>
        </form>
    </div>

    <div class="col-lg-9">
        <div class="row g-4">
            @forelse($courses as $course)
                <div class="col-md-6 col-xl-4 ed-reveal">
                    @include('partials.course-card', ['course' => $course])
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border">{{ __('No courses found.') }}</div>
                </div>
            @endforelse
        </div>
        <div class="mt-4">{{ $courses->links() }}</div>
    </div>
</div>
@endsection
