@extends('layouts.app')
@php($t = $page->translation())
@section('title', $t?->title.' - '.\App\Services\SettingService::platformName())
@section('description', \Illuminate\Support\Str::limit(strip_tags(str_replace("\n", ' ', $t?->body ?: '')), 160))
@section('content')
<div class="ed-panel p-4 p-lg-5">
    <div class="row align-items-center g-4 g-lg-5">
        <div class="col-lg-6">
            <h1 class="mb-3" style="font-size:clamp(2rem,4vw,2.8rem)">{{ $t?->title }}</h1>
            @foreach (preg_split('/\n+/', trim($t?->body ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $paragraph)
                <p class="text-secondary" style="font-size:1.05rem;line-height:1.8">{{ $paragraph }}</p>
            @endforeach
        </div>
        <div class="col-lg-6">
            <div class="ed-about-media">
                <img src="{{ asset('images/edvora_about.png') }}" alt="{{ $t?->title }}" class="img-fluid w-100">
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-6 col-lg-3">
            <div class="ed-about-feature">
                <div class="ed-about-feature__icon"><i class="bi bi-people"></i></div>
                <h3>{{ __('about-feature-learner-title') }}</h3>
                <p>{{ __('about-feature-learner-desc') }}</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="ed-about-feature">
                <div class="ed-about-feature__icon"><i class="bi bi-journal-bookmark"></i></div>
                <h3>{{ __('about-feature-courses-title') }}</h3>
                <p>{{ __('about-feature-courses-desc') }}</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="ed-about-feature">
                <div class="ed-about-feature__icon"><i class="bi bi-easel2"></i></div>
                <h3>{{ __('about-feature-instructor-title') }}</h3>
                <p>{{ __('about-feature-instructor-desc') }}</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="ed-about-feature">
                <div class="ed-about-feature__icon"><i class="bi bi-shield-check"></i></div>
                <h3>{{ __('about-feature-platform-title') }}</h3>
                <p>{{ __('about-feature-platform-desc') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
