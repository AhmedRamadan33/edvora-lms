@extends('layouts.app')
@section('content')
<div class="row justify-content-center"><div class="col-md-5"><div class="card border-0 shadow-sm"><div class="card-body p-4">
<h1 class="h4 mb-3">{{ __('Reset Password') }}</h1>
<form method="POST" action="{{ route('password.store') }}">@csrf
<input type="hidden" name="token" value="{{ $request->route('token') }}">
<input type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control mb-3" required>
<input type="password" name="password" class="form-control mb-3" required>
<input type="password" name="password_confirmation" class="form-control mb-3" required>
<button class="btn btn-primary w-100">{{ __('Reset Password') }}</button>
</form>
</div></div></div></div>
@endsection
