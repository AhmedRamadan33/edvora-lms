@extends('layouts.panel')
@section('heading', __('Contact message'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __($contact->subject) ?: __('Contact message') }}</h2>
        <p>{{ $contact->name }} · {{ $contact->email }}</p>
    </div>
    <a href="{{ route('admin.contacts.index') }}" class="btn btn-outline-primary btn-sm">{{ __('Back') }}</a>
</div>

<div class="ed-panel p-4 mb-3">
    <div class="small text-muted mb-2">{{ $contact->created_at }}</div>
    <div style="white-space:pre-wrap;line-height:1.7">{{ $contact->message }}</div>
</div>

<form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}" data-confirm-delete>
    @csrf @method('DELETE')
    <button class="btn btn-outline-primary">{{ __('Delete') }}</button>
</form>
@endsection
