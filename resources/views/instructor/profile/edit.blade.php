@extends('layouts.panel')
@section('heading', __('Instructor profile'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Public instructor profile') }}</h2>
        <p>
            {{ __('This information appears on your public seller page.') }}
            <span class="ed-status is-{{ $profile->status }} ms-1">{{ __status($profile->status) }}</span>
        </p>
    </div>
</div>

<form method="POST" action="{{ route('instructor.profile.update') }}" class="ed-panel p-4">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="form-label">{{ __('Headline') }}</label>
        <input name="headline" class="form-control" value="{{ old('headline', $profile->headline) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">{{ __('About') }}</label>
        <textarea name="about" class="form-control" rows="5">{{ old('about', $profile->about) }}</textarea>
    </div>
    <div class="mb-4">
        <label class="form-label">{{ __('Website') }}</label>
        <input name="website" class="form-control" value="{{ old('website', $profile->website) }}" placeholder="https://">
    </div>
    <button class="btn btn-primary">{{ __('Save') }}</button>
</form>
@endsection
