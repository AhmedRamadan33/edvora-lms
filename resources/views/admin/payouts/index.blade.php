@extends('layouts.panel')
@section('heading', __('Payouts'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Payout requests') }}</h2>
        <p>{{ __('Process instructor withdrawals securely.') }}</p>
    </div>
</div>

<x-table-toolbar :placeholder="__('Search payouts')" />
<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Instructor') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Method') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payouts as $payout)
                    <tr>
                        <td>{{ $payouts->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold">{{ $payout->instructor->name }}</td>
                        <td>{{ number_format($payout->amount, 2) }}</td>
                        <td>
                            <div>{{ $payout->method }}</div>
                            <div class="small text-muted">{{ $payout->account_details }}</div>
                        </td>
                        <td><span class="ed-status is-{{ $payout->status }}">{{ __status($payout->status) }}</span></td>
                        <td class="text-end pe-3">
                            @if($payout->status === 'pending')
                                <form method="POST" action="{{ route('admin.payouts.approve', $payout) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success">{{ __('Pay') }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.payouts.reject', $payout) }}" class="d-inline-flex gap-1 mt-1">
                                    @csrf
                                    <input name="admin_note" class="form-control form-control-sm" required placeholder="{{ __('Note') }}">
                                    <button class="btn btn-sm btn-outline-primary">{{ __('Reject') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$payouts" />
@endsection
