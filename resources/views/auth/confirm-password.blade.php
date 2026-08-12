@extends('layouts.app')
@section('content')
<div class="row justify-content-center"><div class="col-md-5"><div class="card border-0 shadow-sm"><div class="card-body p-4">
<form method="POST" action="{{ route('password.confirm') }}">@csrf
<input type="password" name="password" class="form-control mb-3" required>
<button class="btn btn-primary w-100">{{ __('Confirm') }}</button>
</form>
</div></div></div></div>
@endsection
