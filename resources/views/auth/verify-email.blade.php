@extends('layouts.app')
@section('content')
<div class="row justify-content-center"><div class="col-md-6"><div class="card border-0 shadow-sm"><div class="card-body p-4">
<p>{{ __('Thanks for signing up! Please verify your email.') }}</p>
<form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-primary">{{ __('Resend Verification Email') }}</button></form>
</div></div></div></div>
@endsection
