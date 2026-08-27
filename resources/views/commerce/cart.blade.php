@extends('layouts.app')
@section('title', __('Cart').' - '.\App\Services\SettingService::platformName())
@section('robots', 'noindex, nofollow')
@section('content')
<div class="ed-section__head mb-4">
    <div>
        <h1 class="mb-1" style="font-size:clamp(2rem,4vw,2.6rem)">{{ __('Your cart') }}</h1>
        <p class="text-muted mb-0">{{ __('Review your selected courses before checkout.') }}</p>
    </div>
</div>

<div class="ed-panel overflow-hidden">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th class="ps-4">{{ __('Course') }}</th>
                    <th>{{ __('Price') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-semibold">{{ $item->course->translation()?->title }}</div>
                            <div class="small text-muted">{{ $item->course->instructor?->name }}</div>
                        </td>
                        <td>{{ money($item->course->price) }}</td>
                        <td class="pe-4 text-end">
                            <form method="POST" action="{{ route('cart.destroy', $item->course) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-primary">{{ __('Remove') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-4 text-muted">{{ __('Cart is empty.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->isNotEmpty())
        <div class="d-flex justify-content-between align-items-center p-4 border-top">
            <strong class="fs-5">{{ __('Subtotal') }}: {{ number_format($subtotal, 2) }}</strong>
            <a href="{{ route('checkout.show') }}" class="btn btn-primary">{{ __('Checkout') }}</a>
        </div>
    @endif
</div>
@endsection
