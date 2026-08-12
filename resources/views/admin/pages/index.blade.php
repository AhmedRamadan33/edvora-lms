@extends('layouts.panel')
@section('heading', __('CMS Pages'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('CMS pages') }}</h2>
        <p>{{ __('Manage About, Terms, Privacy, and FAQ content.') }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.pages.store') }}" class="ed-panel p-4 mb-4">
    @csrf
    <h3 class="h6 mb-3">{{ __('Create page') }}</h3>
    <div class="row g-3">
        <div class="col-md-4"><input name="slug" class="form-control" placeholder="{{ __('Slug') }}"></div>
        <div class="col-md-4"><input name="title_en" class="form-control" placeholder="{{ __('Title EN') }}" required></div>
        <div class="col-md-4"><input name="title_ar" class="form-control" placeholder="{{ __('Title AR') }}" required></div>
        <div class="col-md-6"><textarea name="body_en" class="form-control" rows="3" placeholder="{{ __('Body EN') }}"></textarea></div>
        <div class="col-md-6"><textarea name="body_ar" class="form-control" rows="3" placeholder="{{ __('Body AR') }}"></textarea></div>
        <div class="col-12"><button class="btn btn-primary">{{ __('Create page') }}</button></div>
    </div>
</form>

@foreach($pages as $page)
    @php
        $en = $page->translations->firstWhere('locale', 'en');
        $ar = $page->translations->firstWhere('locale', 'ar');
    @endphp
    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="ed-panel p-4 mb-3">
        @csrf @method('PUT')
        <div class="d-flex justify-content-between align-items-center mb-3">
            <strong class="display-font">{{ $page->slug }}</strong>
            <span class="ed-status is-{{ $page->is_published ? 'published' : 'draft' }}">
                {{ $page->is_published ? __('Published') : __('Draft') }}
            </span>
        </div>
        <div class="row g-3">
            <div class="col-md-6"><input name="title_en" class="form-control" value="{{ $en?->title }}" required></div>
            <div class="col-md-6"><input name="title_ar" class="form-control" value="{{ $ar?->title }}" required></div>
            <div class="col-md-6"><textarea name="body_en" class="form-control" rows="4">{{ $en?->body }}</textarea></div>
            <div class="col-md-6"><textarea name="body_ar" class="form-control" rows="4">{{ $ar?->body }}</textarea></div>
            <div class="col-12 d-flex align-items-center gap-3">
                <label class="form-check mb-0">
                    <input type="checkbox" name="is_published" value="1" class="form-check-input" @checked($page->is_published)>
                    {{ __('Published') }}
                </label>
                <button class="btn btn-sm btn-primary">{{ __('Update') }}</button>
            </div>
        </div>
    </form>
@endforeach
{{ $pages->links() }}
@endsection
