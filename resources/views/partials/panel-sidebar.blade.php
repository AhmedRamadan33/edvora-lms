@php($user = auth()->user())
@if($user?->hasRole('admin'))
    @include('admin.partials.nav')
@elseif($user?->hasRole('instructor'))
    @include('instructor.partials.nav')
@else
    @include('student.partials.nav')
@endif
