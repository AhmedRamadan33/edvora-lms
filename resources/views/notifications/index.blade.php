@extends('layouts.panel')
@section('heading', __('Notifications'))
@section('title', __('Notifications'))
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Notifications') }}</h2>
        <p>{{ __('Everything relevant to your account, in one place.') }}</p>
    </div>
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('Mark all as read') }}</button>
    </form>
</div>

<div class="ed-table-wrap">
    <div class="list-group list-group-flush">
        @forelse($items as $item)
            <a href="{{ route('notifications.read', $item->id) }}"
                class="list-group-item list-group-item-action d-flex justify-content-between align-items-start gap-3 {{ $item->read_at ? '' : 'ed-notif-item is-unread' }}">
                <div>
                    <div class="ed-notif-item__message">{{ $item->data['message'] ?? '' }}</div>
                    <div class="ed-notif-item__time">{{ $item->created_at->diffForHumans() }}</div>
                </div>
                @unless($item->read_at)
                    <span class="badge bg-primary rounded-pill align-self-center">{{ __('New') }}</span>
                @endunless
            </a>
        @empty
            <div class="p-4 text-muted">{{ __('No notifications yet.') }}</div>
        @endforelse
    </div>
</div>

<x-table-pagination :paginator="$items" />
@endsection
