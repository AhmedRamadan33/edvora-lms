@extends('layouts.panel')
@section('heading', __('Coupons'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Coupons') }}</h2>
        <p>{{ __('Create discount codes for campaigns and launches.') }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <form method="POST" action="{{ route('admin.coupons.store') }}" class="ed-panel p-4">
            @csrf
            <h3 class="h6 mb-3">{{ __('Create coupon') }}</h3>
            <input name="code" class="form-control mb-2" placeholder="{{ __('Code') }}" required>
            <select name="type" class="form-select mb-2">
                <option value="percent">{{ __('Percent') }}</option>
                <option value="fixed">{{ __('Fixed') }}</option>
            </select>
            <input name="value" type="number" step="0.01" class="form-control mb-2" placeholder="{{ __('Value') }}" required>
            <input name="min_amount" type="number" step="0.01" class="form-control mb-2" placeholder="{{ __('Min amount') }}">
            <input name="max_uses" type="number" class="form-control mb-3" placeholder="{{ __('Max uses') }}">
            <button class="btn btn-primary w-100">{{ __('Create') }}</button>
        </form>
    </div>
    <div class="col-lg-8">
        <x-table-toolbar :placeholder="__('Search coupons')" />
        <div class="ed-table-wrap">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Value') }}</th>
                            <th>{{ __('Used') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($coupons as $coupon)
                            <tr>
                                <td>{{ $coupons->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $coupon->code }}</td>
                                <td>{{ __($coupon->type) }}</td>
                                <td>{{ $coupon->value }}</td>
                                <td>{{ $coupon->used_count }}</td>
                                <td class="text-end pe-3">
                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" data-confirm-delete>
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-primary">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <x-table-pagination :paginator="$coupons" />
    </div>
</div>
@endsection
