<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ \App\Services\SettingService::platformName() }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('edvora-logo.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        .ed-page-loader{position:fixed;inset:0;z-index:99999;display:grid;place-items:center;background:#f7f9fc;transition:opacity .35s ease,visibility .35s ease}
        .ed-page-loader.is-done{opacity:0;visibility:hidden;pointer-events:none}
        .ed-page-loader__logo{width:min(220px,52vw);height:auto;display:block;animation:ed-loader-pulse 1.4s ease-in-out infinite}
        @keyframes ed-loader-pulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(.97);opacity:.85}}
    </style>
    @if(app()->getLocale() === 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="ed-panel-body">
@include('partials.page-loader')
@php
    $platform = \App\Services\SettingService::platformName();
    $sidebarHtml = trim($__env->yieldContent('sidebar'));
    if ($sidebarHtml === '') {
        $sidebarHtml = view('partials.panel-sidebar')->render();
    }
@endphp

<div class="app-shell">
    <aside class="app-sidebar d-none d-lg-flex flex-column">
        @include('partials.brand', ['class' => 'brand ed-brand ed-brand--panel', 'platform' => $platform, 'href' => route('dashboard')])
        <nav class="nav flex-column flex-grow-1">
            {!! $sidebarHtml !!}
        </nav>
        <div class="app-sidebar__foot">
            <a class="nav-link @if(request()->routeIs('profile.*')) active @endif" href="{{ route('profile.edit') }}">
                <i class="bi bi-person-gear me-2"></i>{{ __('Profile settings') }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
                    <i class="bi bi-box-arrow-right me-2"></i>{{ __('Log Out') }}
                </button>
            </form>
        </div>
    </aside>

    <div class="app-content">
        <div class="app-topbar">
            <div class="d-flex align-items-center gap-3 min-w-0">
                <button class="btn btn-sm btn-outline-primary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#panelNav" aria-label="Menu">
                    <i class="bi bi-list"></i>
                </button>
                <div class="min-w-0">
                    <div class="app-topbar__title text-truncate">@yield('heading', __('Dashboard'))</div>
                    <div class="text-muted small text-truncate">{{ auth()->user()->name }}</div>
                </div>
            </div>

            <div class="app-topbar__actions d-flex flex-row flex-nowrap align-items-center gap-2"
                style="display:flex;flex-direction:row;flex-wrap:nowrap;align-items:center;gap:.5rem;">
                @include('partials.locale-dropdown', [
                    'asItem' => false,
                    'toggleClass' => 'btn btn-sm btn-outline-primary ed-locale-toggle dropdown-toggle',
                ])

                <a href="{{ route('home') }}" class="btn btn-sm btn-outline-primary text-nowrap">{{ __('Storefront') }}</a>

                <div class="dropdown" style="display:inline-flex;">
                    <button class="btn btn-sm btn-primary dropdown-toggle d-inline-flex align-items-center gap-2 text-nowrap" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="ed-nav__avatar ed-nav__avatar--sm">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                        <span>{{ __('Account') }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('Profile settings') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('home') }}">{{ __('Storefront') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">{{ __('Log Out') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="app-page">
            @yield('content')
        </div>
    </div>
</div>

@include('partials.flash')

<div class="offcanvas offcanvas-start app-offcanvas" tabindex="-1" id="panelNav">
    <div class="offcanvas-header">
        @include('partials.brand', ['class' => 'brand ed-brand ed-brand--panel', 'platform' => $platform, 'href' => route('dashboard')])
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <nav class="nav flex-column">
            {!! $sidebarHtml !!}
            <a class="nav-link @if(request()->routeIs('profile.*')) active @endif" href="{{ route('profile.edit') }}">
                <i class="bi bi-person-gear me-2"></i>{{ __('Profile settings') }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
                    <i class="bi bi-box-arrow-right me-2"></i>{{ __('Log Out') }}
                </button>
            </form>
        </nav>
    </div>
</div>
</body>
</html>
