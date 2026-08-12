@extends('layouts.panel')
@section('heading', __('Testimonials'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Customer testimonials') }}</h2>
        <p>{{ __('Manage reviews shown on the homepage and testimonials page.') }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.testimonials.store') }}" class="ed-panel p-4 mb-4">
    @csrf
    <h3 class="h6 mb-3">{{ __('Add testimonial') }}</h3>
    <div class="row g-3">
        <div class="col-md-4"><input name="name" class="form-control" placeholder="{{ __('Name') }}" required></div>
        <div class="col-md-4"><input name="role" class="form-control" placeholder="{{ __('Role') }}"></div>
        <div class="col-md-2"><input type="number" name="rating" class="form-control" value="5" min="1" max="5" required></div>
        <div class="col-md-2"><input type="number" name="sort_order" class="form-control" value="0" min="0"></div>
        <div class="col-md-6"><textarea name="content_en" class="form-control" rows="3" placeholder="{{ __('Content EN') }}" required></textarea></div>
        <div class="col-md-6"><textarea name="content_ar" class="form-control" rows="3" placeholder="{{ __('Content AR') }}" required></textarea></div>
        <div class="col-md-6">
            <label class="form-check"><input type="checkbox" name="is_published" value="1" class="form-check-input" checked> {{ __('Published') }}</label>
        </div>
        <div class="col-md-6">
            <label class="form-check"><input type="checkbox" name="show_on_home" value="1" class="form-check-input" checked> {{ __('Show on home') }}</label>
        </div>
        <div class="col-12"><button class="btn btn-primary">{{ __('Create') }}</button></div>
    </div>
</form>

@foreach($testimonials as $testimonial)
    <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" class="ed-panel p-4 mb-3">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-4"><input name="name" class="form-control" value="{{ $testimonial->name }}" required></div>
            <div class="col-md-4"><input name="role" class="form-control" value="{{ $testimonial->role }}"></div>
            <div class="col-md-2"><input type="number" name="rating" class="form-control" value="{{ $testimonial->rating }}" min="1" max="5" required></div>
            <div class="col-md-2"><input type="number" name="sort_order" class="form-control" value="{{ $testimonial->sort_order }}" min="0"></div>
            <div class="col-md-6"><textarea name="content_en" class="form-control" rows="3" required>{{ $testimonial->content_en }}</textarea></div>
            <div class="col-md-6"><textarea name="content_ar" class="form-control" rows="3" required>{{ $testimonial->content_ar }}</textarea></div>
            <div class="col-md-4">
                <label class="form-check"><input type="checkbox" name="is_published" value="1" class="form-check-input" @checked($testimonial->is_published)> {{ __('Published') }}</label>
            </div>
            <div class="col-md-4">
                <label class="form-check"><input type="checkbox" name="show_on_home" value="1" class="form-check-input" @checked($testimonial->show_on_home)> {{ __('Show on home') }}</label>
            </div>
            <div class="col-md-4 d-flex gap-2 justify-content-md-end">
                <button class="btn btn-sm btn-primary">{{ __('Update') }}</button>
                <button form="delete-testimonial-{{ $testimonial->id }}" class="btn btn-sm btn-outline-primary">{{ __('Delete') }}</button>
            </div>
        </div>
    </form>
    <form id="delete-testimonial-{{ $testimonial->id }}" method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" data-confirm-delete>
        @csrf @method('DELETE')
    </form>
@endforeach
<x-table-pagination :paginator="$testimonials" />
@endsection
