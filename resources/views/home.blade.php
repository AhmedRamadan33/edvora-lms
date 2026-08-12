@extends('layouts.app')

@section('title', \App\Services\SettingService::platformName())
@section('main_class', 'is-flush')

@section('fullwidth')
<section class="ed-hero">
    <div class="ed-hero__media" aria-hidden="true"></div>
    <div class="ed-hero__glow" aria-hidden="true"></div>
    <div class="ed-hero__content">
        <h1 class="ed-hero__brand">Edvora</h1>
        <p class="ed-hero__headline">{{ __('Master skills with world-class instructors') }}</p>
        <p class="ed-hero__text">{{ __('A marketplace built for serious learners and expert teachers.') }}</p>
        <div class="ed-hero__actions">
            <a href="{{ route('courses.index') }}" class="btn btn-light btn-lg">{{ __('Browse catalog') }}</a>
            <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">{{ __('Start teaching') }}</a>
        </div>
    </div>
</section>

<section class="ed-section why-section">
    <div class="ed-container">
        <div class="why-section__head ed-reveal">
            <h2>{{ __('Why choose Edvora?') }}</h2>
            <p>{{ __('A unique learning experience that combines quality, flexibility, and fair pricing.') }}</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4 ed-reveal">
                <article class="why-card">
                    <div class="why-card__icon"><i class="bi bi-laptop"></i></div>
                    <h3>{{ __('Learn anytime') }}</h3>
                    <p>{{ __('Courses are available around the clock. Learn on your schedule from anywhere.') }}</p>
                </article>
            </div>
            <div class="col-md-4 ed-reveal">
                <article class="why-card">
                    <div class="why-card__icon"><i class="bi bi-person-workspace"></i></div>
                    <h3>{{ __('Professional instructors') }}</h3>
                    <p>{{ __('Learn from top experts and specialists with years of practical experience.') }}</p>
                </article>
            </div>
            <div class="col-md-4 ed-reveal">
                <article class="why-card">
                    <div class="why-card__icon"><i class="bi bi-award"></i></div>
                    <h3>{{ __('Accredited certificates') }}</h3>
                    <p>{{ __('Earn certificates after completing each course to boost your career opportunities.') }}</p>
                </article>
            </div>
        </div>
    </div>
</section>

@if($featured->isNotEmpty())
<section class="ed-section" style="padding-top: 0;">
    <div class="ed-container">
        <div class="ed-section__head ed-reveal">
            <div>
                <h2>{{ __('Featured courses') }}</h2>
                <p>{{ __('Hand-picked learning paths from top instructors.') }}</p>
            </div>
            <a href="{{ route('courses.index') }}" class="btn btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="row g-4">
            @foreach($featured as $course)
                <div class="col-md-6 col-xl-3 ed-reveal">
                    @include('partials.course-card', ['course' => $course])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="ed-section" style="padding-top: 0;">
    <div class="ed-container">
        <div class="ed-section__head ed-reveal">
            <div>
                <h2>{{ __('Latest courses') }}</h2>
                <p>{{ __('Fresh courses published across the marketplace.') }}</p>
            </div>
        </div>
        <div class="row g-4">
            @forelse($courses as $course)
                <div class="col-md-6 col-xl-3 ed-reveal">
                    @include('partials.course-card', ['course' => $course])
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-light border">{{ __('No courses yet.') }}</div>
                </div>
            @endforelse
        </div>
    </div>
</section>

@if($testimonials->isNotEmpty())
<section class="ed-section testimonials-section">
    <div class="ed-container">
        <div class="ed-section__head ed-reveal">
            <div>
                <h2>{{ __('What learners say') }}</h2>
                <p>{{ __('Trusted by students building real careers.') }}</p>
            </div>
            <a href="{{ route('testimonials.index') }}" class="btn btn-outline-primary">{{ __('All testimonials') }}</a>
        </div>
        <div class="row g-4">
            @foreach($testimonials as $testimonial)
                <div class="col-md-6 col-xl-4 ed-reveal">
                    <article class="testimonial-card h-100">
                        <div class="testimonial-card__stars mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $testimonial->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </div>
                        <p class="testimonial-card__content">“{{ $testimonial->content() }}”</p>
                        <div class="testimonial-card__author">
                            <div class="testimonial-card__avatar">{{ mb_substr($testimonial->name, 0, 1) }}</div>
                            <div>
                                <strong>{{ $testimonial->name }}</strong>
                                <div class="small text-muted">{{ $testimonial->role }}</div>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(isset($categories) && $categories->isNotEmpty())
<section class="ed-section" style="padding-top: 0;">
    <div class="ed-container">
        <div class="ed-section__head ed-reveal">
            <div>
                <h2>{{ __('Browse by category') }}</h2>
                <p>{{ __('Find the path that matches your goals.') }}</p>
            </div>
        </div>
        <div class="row g-3">
            @foreach($categories as $category)
                <div class="col-6 col-md-3 ed-reveal">
                    <a href="{{ route('courses.index', ['category' => $category->slug]) }}" class="course-tile" style="padding:1.25rem; min-height:120px; justify-content:center;">
                        <div class="d-flex align-items-center gap-2">
                            @if($category->icon)<i class="bi {{ $category->icon }} fs-4" style="color:var(--ed-accent)"></i>@endif
                            <strong class="display-font" style="font-size:1.1rem">{{ $category->translation()?->name }}</strong>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
