@php
    $brandClass = $class ?? 'ed-brand';
    $showText = $text ?? true;
    $platform = $platform ?? \App\Services\SettingService::platformName();
@endphp
<a class="{{ $brandClass }}" href="{{ $href ?? route('home') }}">
    <img src="{{ asset('edvora-logo.svg') }}" alt="{{ $platform }}" class="ed-brand__mark" width="36" height="36">
    @if ($showText)
        <span class="ed-brand__text">{{ $platform }}<span>.</span></span>
    @endif
</a>
