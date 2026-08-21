@extends('layouts.panel')
@section('heading', __('Add instructor'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Add instructor') }}</h2>
        <p>{{ __('Create an instructor account directly. They will get an email to set their own password.') }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.instructors.store') }}" class="ed-panel p-4">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">{{ __('Name') }}</label>
            <input name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Email') }}</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Phone') }}</label>
            <input name="phone" class="form-control" value="{{ old('phone') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">{{ __('Headline') }}</label>
            <input name="headline" class="form-control" value="{{ old('headline') }}" placeholder="{{ __('e.g. Senior Web Developer') }}">
        </div>
        <div class="col-12">
            <button class="btn btn-primary">{{ __('Create instructor') }}</button>
        </div>
    </div>
</form>
@endsection
