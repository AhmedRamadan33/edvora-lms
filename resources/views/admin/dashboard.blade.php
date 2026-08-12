@extends('layouts.panel')
@section('heading', __('Admin Dashboard'))
@section('sidebar')
@include('admin.partials.nav')
@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Platform overview') }}</h2>
        <p>{{ __('Monitor marketplace performance at a glance.') }}</p>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach($stats as $label => $value)
        <div class="col-md-4 col-xl-2">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ __(str_replace('_', ' ', ucfirst($label))) }}</div>
                    <div class="fs-4 fw-bold mt-1">
                        {{ is_numeric($value) ? number_format($value, (floor($value) != $value ? 2 : 0)) : $value }}
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="ed-table-wrap">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h5 mb-0">{{ __('Recent orders') }}</h3>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Reports') }}</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->number }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ number_format($order->total, 2) }} {{ $order->currency }}</td>
                        <td><span class="ed-status is-{{ $order->status }}">{{ __status($order->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted p-4">{{ __('No orders yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
