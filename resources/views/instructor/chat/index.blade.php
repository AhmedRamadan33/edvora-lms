@extends('layouts.panel')
@section('heading', __('Messages'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
@include('partials.chat-app', [
    'routePrefix' => 'instructor.chat',
    'pickerLabel' => __('Student'),
    'pickerUsers' => $students,
])
@endsection
