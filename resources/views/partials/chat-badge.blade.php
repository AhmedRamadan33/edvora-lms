@php
    $chatIndexRoute = auth()->user()?->hasRole('instructor') ? 'instructor.chat.index' : 'student.chat.index';
    $toggleClass = $toggleClass ?? 'btn btn-sm btn-outline-primary';
@endphp
<a href="{{ route($chatIndexRoute) }}" class="{{ $toggleClass }} position-relative" aria-label="{{ __('Messages') }}"
    data-chat-badge data-unread-url="{{ route('chat.unread-count') }}">
    <i class="bi bi-chat-dots"></i>
    <span class="ed-notif-badge badge rounded-pill bg-danger d-none" data-chat-badge-count>0</span>
</a>
