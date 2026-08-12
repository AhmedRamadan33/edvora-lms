@extends('layouts.panel')
@section('heading', __('Earnings'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Earnings & payouts') }}</h2>
        <p>{{ __('Review your available balance and withdrawal history.') }}</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="text-muted">{{ __('Available balance') }}</div>
                <div class="fs-3 fw-bold mt-1">{{ number_format($available, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('instructor.earnings.payout') }}" class="ed-panel p-4 mb-4">
    @csrf
    <h3 class="h6 mb-3">{{ __('Request payout') }}</h3>
    <div class="row g-2">
        <div class="col-md-3">
            <input type="number" step="0.01" name="amount" class="form-control" placeholder="{{ __('Amount') }}" required>
        </div>
        <div class="col-md-3">
            <input name="method" class="form-control" placeholder="PayPal / Bank" required>
        </div>
        <div class="col-md-4">
            <input name="account_details" class="form-control" placeholder="{{ __('Account details') }}" required>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">{{ __('Request') }}</button>
        </div>
    </div>
</form>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="ed-table-wrap">
            <div class="p-3 border-bottom"><h3 class="h6 mb-0">{{ __('Earnings') }}</h3></div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Course') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($earnings as $earning)
                            <tr>
                                <td>{{ $earnings->firstItem() + $loop->index }}</td>
                                <td>{{ $earning->course->translation()?->title }}</td>
                                <td>{{ number_format($earning->amount, 2) }}</td>
                                <td><span class="ed-status is-{{ $earning->status }}">{{ __status($earning->status) }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('instructor.courses.edit', $earning->course) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <x-table-pagination :paginator="$earnings" />
    </div>
    <div class="col-lg-5">
        <div class="ed-panel p-4">
            <h3 class="h6 mb-3">{{ __('Payout requests') }}</h3>
            <ul class="list-group list-group-flush">
                @forelse($payouts as $payout)
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>{{ number_format($payout->amount, 2) }} · {{ $payout->method }}</span>
                        <span class="ed-status is-{{ $payout->status }}">{{ __status($payout->status) }}</span>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-muted">{{ __('No payout requests yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
