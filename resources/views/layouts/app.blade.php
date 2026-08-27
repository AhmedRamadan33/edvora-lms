<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="HNDDrb5cQoensf_rR5XK7xu2vfQ7wZ0mLflPLRLvyR0" />
    <title>@yield('title', \App\Services\SettingService::platformName())</title>
    <meta name="description" content="@yield('description', __('edvora-description'))">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ \App\Services\SettingService::platformName() }}">
    <meta property="og:title" content="@yield('title', \App\Services\SettingService::platformName())">
    <meta property="og:description" content="@yield('description', __('edvora-description'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('image', asset('edvora-logo.svg'))">
    <meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_AR' : 'en_US' }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', \App\Services\SettingService::platformName())">
    <meta name="twitter:description" content="@yield('description', __('edvora-description'))">
    <meta name="twitter:image" content="@yield('image', asset('edvora-logo.svg'))">
    <link rel="icon" type="image/svg+xml" href="{{ asset('edvora-logo.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        .ed-page-loader {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: grid;
            place-items: center;
            background: #f7f9fc;
            transition: opacity .35s ease, visibility .35s ease
        }

        .ed-page-loader.is-done {
            opacity: 0;
            visibility: hidden;
            pointer-events: none
        }

        .ed-page-loader__logo {
            width: min(220px, 52vw);
            height: auto;
            display: block;
            animation: ed-loader-pulse 1.4s ease-in-out infinite
        }

        @keyframes ed-loader-pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1
            }

            50% {
                transform: scale(.97);
                opacity: .85
            }
        }
    </style>
    @if (app()->getLocale() === 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => \App\Services\SettingService::platformName(),
            'url' => url('/'),
            'logo' => asset('edvora-logo.svg'),
            'email' => \App\Services\SettingService::platformEmail(),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @stack('structured_data')
</head>

<body>
    @include('partials.page-loader')
    @php($platform = \App\Services\SettingService::platformName())

    <header class="ed-nav">
        <nav class="navbar navbar-expand-lg">
            <div class="ed-container d-flex flex-wrap align-items-center w-100">
                <a class="ed-brand navbar-brand mb-0 me-lg-3" href="{{ route('home') }}">
                    <img src="{{ asset('edvora-logo.svg') }}" alt="{{ $platform }}" class="ed-brand__mark"
                        width="34" height="34">
                    <span class="ed-brand__text">{{ $platform }}</span>
                </a>

                <button class="navbar-toggler border-0 shadow-none ms-auto" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Menu">
                    <i class="bi bi-list fs-3"></i>
                </button>

                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav mx-lg-auto my-2 my-lg-0 gap-lg-1">
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('home')) active @endif"
                                href="{{ route('home') }}">{{ __('Home') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('courses.*')) active @endif"
                                href="{{ route('courses.index') }}">{{ __('Courses') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pages.show', 'about') }}">{{ __('About') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('testimonials.*')) active @endif"
                                href="{{ route('testimonials.index') }}">{{ __('Testimonials') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if (request()->routeIs('contact.*')) active @endif"
                                href="{{ route('contact.show') }}">{{ __('Contact') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pages.show', 'faq') }}">{{ __('FAQ') }}</a>
                        </li>
                    </ul>

                    <ul class="navbar-nav align-items-lg-center gap-lg-1 pb-2 pb-lg-0">
                        @include('partials.locale-dropdown')

                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('cart.index') }}" aria-label="{{ __('Cart') }}">
                                    <i class="bi bi-bag"></i>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('wishlist.index') }}"
                                    aria-label="{{ __('Wishlist') }}">
                                    <i class="bi bi-heart"></i>
                                </a>
                            </li>
                            @include('partials.notifications-bell')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-inline-flex align-items-center gap-2" href="#"
                                    data-bs-toggle="dropdown">
                                    <span class="ed-nav__avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                                    <span>{{ auth()->user()->name }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                                    </li>
                                    <li><a class="dropdown-item"
                                            href="{{ route('profile.edit') }}">{{ __('Profile settings') }}</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item">{{ __('Log Out') }}</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Log in') }}</a>
                            </li>
                            <li class="nav-item ms-lg-2">
                                <a class="btn btn-primary btn-sm"
                                    href="{{ route('register') }}">{{ __('Get started') }}</a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="ed-main @yield('main_class')">
        @hasSection('fullwidth')
            @yield('fullwidth')
        @else
            <div class="ed-container py-4">
                @yield('content')
            </div>
        @endif
    </main>

    <footer class="ed-footer">
        <div class="ed-container">
            <div class="row g-4 align-items-start">
                <div class="col-lg-5">
                    <div class="ed-footer__brand mb-2 d-flex align-items-center gap-2">
                        <img src="{{ asset('edvora-logo.svg') }}" alt="" width="28" height="28">
                        <span>{{ $platform }}</span>
                    </div>
                    <p class="mb-0">{{ __('edvora-description') }}</p>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="text-white fw-semibold mb-2">{{ __('Explore') }}</div>
                    <div class="d-grid gap-1">
                        <a href="{{ route('courses.index') }}">{{ __('Courses') }}</a>
                        <a href="{{ route('pages.show', 'about') }}">{{ __('About') }}</a>
                        <a href="{{ route('testimonials.index') }}">{{ __('Testimonials') }}</a>
                        <a href="{{ route('contact.show') }}">{{ __('Contact') }}</a>
                        <a href="{{ route('pages.show', 'faq') }}">{{ __('FAQ') }}</a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="text-white fw-semibold mb-2">{{ __('Legal') }}</div>
                    <div class="d-grid gap-1">
                        <a href="{{ route('pages.show', 'terms') }}">{{ __('Terms') }}</a>
                        <a href="{{ route('pages.show', 'privacy') }}">{{ __('Privacy') }}</a>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="text-white fw-semibold mb-2">{{ __('Contact') }}</div>
                    <p>{{ \App\Services\SettingService::platformPhone() }}</p>
                    <p>{{ \App\Services\SettingService::platformEmail() }}</p>
                </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between gap-2 pt-4 mt-4 ed-footer__meta">
                <div>&copy; {{ date('Y') }} {{ $platform }}</div>
                <div>{{ __('Crafted for serious learning') }}</div>
            </div>
        </div>
    </footer>

    @include('partials.flash')
</body>

</html>
