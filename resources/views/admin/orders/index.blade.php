@extends('layouts.panel')
@section('heading', __('Orders'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Completed orders') }}</h2>
        <p>{{ __('View paid orders and how each checkout was completed.') }}</p>
    </div>
</div>

<x-table-toolbar :placeholder="__('Search orders')">
    <select name="payment_method" class="form-select w-auto">
        <option value="">{{ __('All payment methods') }}</option>
        @foreach (['stripe', 'paymob'] as $method)
            <option value="{{ $method }}" @selected(request('payment_method') === $method)>{{ __status($method) }}</option>
        @endforeach
    </select>
</x-table-toolbar>

<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Order number') }}</th>
                    <th>{{ __('Student') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Payment method') }}</th>
                    <th>{{ __('Date') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $orders->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold">{{ $order->number }}</td>
                        <td>{{ $order->user?->name ?? '—' }}</td>
                        <td>{{ $order->user?->email ?? '—' }}</td>
                        <td>{{ money($order->total, $order->currency) }}</td>
                        <td>{{ __status($order->payment_method ?? $order->payment?->provider ?? '—') }}</td>
                        <td class="text-muted">{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted p-4">{{ __('No orders found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$orders" />
@endsection
