<div class="d-flex align-items-center gap-2 my-4">
    <hr class="flex-grow-1">
    <span class="text-muted small">{{ __('or continue with') }}</span>
    <hr class="flex-grow-1">
</div>
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('social.redirect', 'google') }}" class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-google"></i> {{ __('Google') }}
    </a>
    <a href="{{ route('social.redirect', 'facebook') }}" class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-facebook"></i> {{ __('Facebook') }}
    </a>
</div>
