@extends('layouts.panel')
@section('heading', __('Create category'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Create category') }}</h2>
        <p>{{ __('Add bilingual category labels for the catalog.') }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.categories.store') }}" class="ed-panel p-4">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Name EN</label>
            <input name="name_en" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Name AR</label>
            <input name="name_ar" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Description EN</label>
            <textarea name="description_en" class="form-control" rows="3"></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Description AR</label>
            <textarea name="description_ar" class="form-control" rows="3"></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Icon</label>
            <input name="icon" class="form-control" placeholder="bi-code-slash">
        </div>
        <div class="col-md-4">
            <label class="form-label">Sort</label>
            <input type="number" name="sort_order" class="form-control" value="0">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check mb-2">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" checked id="active">
                <label for="active" class="form-check-label">{{ __('Active') }}</label>
            </div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">{{ __('Save') }}</button>
        </div>
    </div>
</form>
@endsection
