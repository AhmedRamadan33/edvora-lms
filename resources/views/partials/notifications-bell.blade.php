@php
    $asItem = $asItem ?? true;
    $toggleClass = $toggleClass ?? 'nav-link';
@endphp
@if ($asItem)
    <li class="nav-item dropdown">
@else
    <div class="dropdown" style="display:inline-flex;">
@endif
    <a class="{{ $toggleClass }} ed-notif-toggle" href="#" id="notifToggle" data-bs-toggle="dropdown"
        aria-expanded="false" role="button" aria-label="{{ __('Notifications') }}">
        <i class="bi bi-bell"></i>
        <span class="ed-notif-badge badge rounded-pill bg-danger d-none" id="notifBadge">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-end ed-notif-menu" aria-labelledby="notifToggle">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <span class="fw-semibold">{{ __('Notifications') }}</span>
            <form method="POST" action="{{ route('notifications.read-all') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-link btn-sm p-0">{{ __('Mark all as read') }}</button>
            </form>
        </div>
        <div id="notifList" class="ed-notif-list"
            data-recent-url="{{ route('notifications.recent') }}"
            data-read-url-template="{{ route('notifications.read', ['notification' => '__ID__']) }}">
            <div class="px-3 py-4 text-center text-muted small" id="notifEmpty">{{ __('No notifications yet.') }}</div>
        </div>
        <div class="text-center border-top">
            <a href="{{ route('notifications.index') }}" class="d-block py-2 small">{{ __('View all') }}</a>
        </div>
    </div>
@if ($asItem)
    </li>
@else
    </div>
@endif
