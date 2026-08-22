@extends('layouts.panel')
@section('heading', __('Messages'))
@section('sidebar')@include('student.partials.nav')@endsection
@section('content')
@include('partials.chat-app', [
    'routePrefix' => 'student.chat',
    'pickerLabel' => __('Instructor'),
    'pickerUsers' => $instructors,
])
@endsection
