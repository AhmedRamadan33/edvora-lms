@extends('layouts.panel')
@section('heading', __('Edit category'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
@php
    $en = $category->translations->firstWhere('locale', 'en');
    $ar = $category->translations->firstWhere('locale', 'ar');
@endphp
<div class="ed-page-head">
    <div>
        <h2>{{ __('Edit category') }}</h2>
        <p>{{ $category->slug }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.categories.update', $category) }}" class="ed-panel p-4">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">{{ __('Name EN') }}</label>
            <input name="name_en" class="form-control" value="{{ $en?->name }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Name AR') }}</label>
            <input name="name_ar" class="form-control" value="{{ $ar?->name }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Description EN') }}</label>
            <textarea name="description_en" class="form-control" rows="3">{{ $en?->description }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Description AR') }}</label>
            <textarea name="description_ar" class="form-control" rows="3">{{ $ar?->description }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('Icon') }}</label>
            <input name="icon" class="form-control" value="{{ $category->icon }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('Sort') }}</label>
            <input type="number" name="sort_order" class="form-control" value="{{ $category->sort_order }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check mb-2">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($category->is_active) id="active">
                <label for="active" class="form-check-label">{{ __('Active') }}</label>
            </div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">{{ __('Save') }}</button>
        </div>
    </div>
</form>
@endsection
