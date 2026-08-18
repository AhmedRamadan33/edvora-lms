<div class="toast-container position-fixed top-0 end-0 p-3 ed-toast-container" style="z-index:100050;">
    @foreach (['success' => 'check-circle-fill', 'error' => 'exclamation-octagon-fill', 'warning' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'] as $type => $icon)
        @if (session($type))
            <div class="toast ed-toast ed-toast--{{ $type }} show"
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
                data-ed-toast>
                <div class="d-flex align-items-start">
                    <div class="toast-body">
                        <i class="bi bi-{{ $icon }} me-2"></i>{{ session($type) }}
                    </div>
                    <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
                </div>
            </div>
        @endif
    @endforeach

    @if ($errors->any())
        <div class="toast ed-toast ed-toast--error show"
            role="alert"
            aria-live="assertive"
            aria-atomic="true"
            data-ed-toast>
            <div class="d-flex align-items-start">
                <div class="toast-body">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i>{{ __('Please correct the highlighted errors.') }}
                    <ul class="mb-0 mt-2 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close me-2 mt-2" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
            </div>
        </div>
    @endif
</div>

<div class="toast ed-toast ed-toast--warning ed-confirm-toast"
    id="ed-confirm-toast"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
    style="z-index:100051;">
    <div class="toast-header">
        <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
        <strong class="me-auto">{{ __('Are you sure?') }}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="toast-body">
        <p class="mb-3" data-confirm-body>{{ __('This action cannot be undone.') }}</p>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-danger" data-confirm-accept data-generic-label="{{ __('Confirm') }}">{{ __('Yes, delete it!') }}</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="toast">{{ __('Cancel') }}</button>
        </div>
    </div>
</div>
