@extends('layouts.app')
@section('title', __('Verify your email').' - '.\App\Services\SettingService::platformName())
@section('robots', 'noindex, nofollow')
@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <div class="mb-4">
            <div class="ed-brand mb-2" style="font-size:1.4rem">Edvora</div>
            <h1>{{ __('Verify your email') }}</h1>
            <p class="text-muted mb-0">{{ __('We sent a 6-digit code to :email. Enter it below to continue.', ['email' => auth()->user()->email]) }}</p>
        </div>

        <form method="POST" action="{{ route('verification.verify') }}" id="otp-form">
            @csrf
            <div class="mb-4 d-flex gap-2 justify-content-center" data-otp-boxes>
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="one-time-code" class="form-control text-center fs-3 p-0" style="width:3rem;height:3.25rem;" data-otp-digit>
                @endfor
            </div>
            <input type="hidden" name="code" id="otp-code-input">
            <button class="btn btn-primary w-100 mb-3" type="submit">{{ __('Verify') }}</button>
        </form>

        <form method="POST" action="{{ route('verification.send') }}" class="text-center small mb-3">
            @csrf
            <button type="submit" class="btn btn-link p-0" id="ed-otp-resend" @if ($secondsUntilResend > 0) disabled @endif>{{ __('Resend code') }}</button>
            <span id="ed-otp-countdown" class="text-muted"></span>
        </form>

        <div class="text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-link p-0 small">{{ __('Log Out') }}</button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var boxes = Array.prototype.slice.call(document.querySelectorAll('[data-otp-digit]'));
    var hidden = document.getElementById('otp-code-input');
    var form = document.getElementById('otp-form');

    function syncHidden() {
        hidden.value = boxes.map(function (box) { return box.value; }).join('');
    }

    boxes.forEach(function (box, index) {
        box.addEventListener('input', function () {
            box.value = box.value.replace(/[^0-9]/g, '');
            if (box.value && index < boxes.length - 1) {
                boxes[index + 1].focus();
            }
            syncHidden();
        });

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !box.value && index > 0) {
                boxes[index - 1].focus();
            }
        });

        box.addEventListener('paste', function (e) {
            e.preventDefault();
            var digits = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').split('');
            boxes.forEach(function (b, i) { b.value = digits[i] || ''; });
            syncHidden();
            var lastIndex = Math.min(digits.length, boxes.length) - 1;
            if (lastIndex >= 0) boxes[lastIndex].focus();
        });
    });

    form.addEventListener('submit', syncHidden);
    if (boxes.length) boxes[0].focus();

    var resendBtn = document.getElementById('ed-otp-resend');
    var countdownEl = document.getElementById('ed-otp-countdown');
    var countdownTemplate = @json(__('Resend available in :seconds s'));
    var seconds = {{ (int) $secondsUntilResend }};

    function tick() {
        if (seconds <= 0) {
            resendBtn.disabled = false;
            countdownEl.textContent = '';
            return;
        }
        resendBtn.disabled = true;
        countdownEl.textContent = ' ' + countdownTemplate.replace(':seconds', seconds);
        seconds -= 1;
        setTimeout(tick, 1000);
    }
    tick();
})();
</script>
@endsection
