<a class="nav-link @if(request()->routeIs('student.dashboard')) active @endif" href="{{ route('student.dashboard') }}">
    <i class="bi bi-journal-bookmark me-2"></i>{{ __('My learning') }}
</a>
<a class="nav-link @if(request()->routeIs('wishlist.*')) active @endif" href="{{ route('wishlist.index') }}">
    <i class="bi bi-heart me-2"></i>{{ __('Wishlist') }}
</a>
<a class="nav-link @if(request()->routeIs('student.certificates.*')) active @endif" href="{{ route('student.certificates.index') }}">
    <i class="bi bi-award me-2"></i>{{ __('Certificates') }}
</a>
<a class="nav-link @if(request()->routeIs('cart.*')) active @endif" href="{{ route('cart.index') }}">
    <i class="bi bi-bag me-2"></i>{{ __('Cart') }}
</a>
