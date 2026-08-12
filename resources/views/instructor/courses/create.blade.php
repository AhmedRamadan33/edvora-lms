@extends('layouts.panel')
@section('heading', __('Create course'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Create a new course') }}</h2>
        <p>{{ __('Start with bilingual basics, then build your curriculum.') }}</p>
    </div>
</div>

<form method="POST" action="{{ route('instructor.courses.store') }}" enctype="multipart/form-data" class="ed-panel p-4">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">{{ __('Title (EN)') }}</label>
            <input name="title_en" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Title (AR)') }}</label>
            <input name="title_ar" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Subtitle (EN)') }}</label>
            <input name="subtitle_en" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Subtitle (AR)') }}</label>
            <input name="subtitle_ar" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Description (EN)') }}</label>
            <textarea name="description_en" class="form-control" rows="4"></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Description (AR)') }}</label>
            <textarea name="description_ar" class="form-control" rows="4"></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('Category') }}</label>
            <select name="category_id" class="form-select">
                <option value="">-</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->translation()?->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('Level') }}</label>
            <select name="level" class="form-select">
                <option value="beginner">{{ __('beginner') }}</option>
                <option value="intermediate">{{ __('intermediate') }}</option>
                <option value="advanced">{{ __('advanced') }}</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('Language') }}</label>
            <select name="language" class="form-select">
                <option value="en">{{ __('en') }}</option>
                <option value="ar">{{ __('ar') }}</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('Price') }}</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('Thumbnail') }}</label>
            <input type="file" name="thumbnail" class="form-control">
        </div>
        <div class="col-12">
            <button class="btn btn-primary">{{ __('Create') }}</button>
        </div>
    </div>
</form>
@endsection
