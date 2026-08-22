<a class="nav-link @if (request()->routeIs('student.dashboard')) active @endif" href="{{ route('student.dashboard') }}">
    <i class="bi bi-journal-bookmark me-2"></i>{{ __('My learning') }}
</a>
<a class="nav-link @if (request()->routeIs('student.chat.*')) active @endif" href="{{ route('student.chat.index') }}">
    <i class="bi bi-chat-dots me-2"></i>{{ __('Messages') }}
</a>
<a class="nav-link @if (request()->routeIs('exams.*')) active @endif" href="{{ route('exams.index') }}">
    <i class="bi bi-file-earmark-text me-2"></i>{{ __('Exams') }}
</a>
<a class="nav-link @if (request()->routeIs('student.certificates.*')) active @endif" href="{{ route('student.certificates.index') }}">
    <i class="bi bi-award me-2"></i>{{ __('Certificates') }}
</a>
<a class="nav-link @if (request()->routeIs('cart.*')) active @endif" href="{{ route('cart.index') }}">
    <i class="bi bi-bag me-2"></i>{{ __('Cart') }}
</a>
<a class="nav-link @if (request()->routeIs('wishlist.*')) active @endif" href="{{ route('wishlist.index') }}">
    <i class="bi bi-heart me-2"></i>{{ __('Wishlist') }}
</a>
