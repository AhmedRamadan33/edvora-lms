@php
    $locale = app()->getLocale();
    $asItem = $asItem ?? true;
    $toggleClass = $toggleClass ?? 'nav-link ed-locale-toggle dropdown-toggle';
@endphp
@if ($asItem)
    <li class="nav-item dropdown">
@else
    <div class="dropdown" style="display:inline-flex;">
@endif
    <a class="{{ $toggleClass }} text-nowrap" href="#" data-bs-toggle="dropdown" aria-expanded="false" role="button">
        <i class="bi bi-globe2"></i>
        <span>{{ $locale === 'ar' ? 'العربية' : 'English' }}</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end ed-locale-menu">
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between @if ($locale === 'en') active @endif"
                href="{{ route('locale.switch', 'en') }}">
                <span>English</span>
                @if ($locale === 'en')
                    <i class="bi bi-check2"></i>
                @endif
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center justify-content-between @if ($locale === 'ar') active @endif"
                href="{{ route('locale.switch', 'ar') }}">
                <span>العربية</span>
                @if ($locale === 'ar')
                    <i class="bi bi-check2"></i>
                @endif
            </a>
        </li>
    </ul>
@if ($asItem)
    </li>
@else
    </div>
@endif
