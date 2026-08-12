@extends('layouts.app')
@section('title', __('Testimonials'))
@section('content')
<div class="ed-page-head mb-4">
    <div>
        <h1 class="mb-1" style="font-size:clamp(2rem,4vw,2.8rem)">{{ __('What learners say') }}</h1>
        <p class="text-muted mb-0">{{ __('Real feedback from students and professionals on Edvora.') }}</p>
    </div>
</div>

<div class="row g-4">
    @forelse($testimonials as $testimonial)
        <div class="col-md-6 col-xl-4">
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
    @empty
        <div class="col-12">
            <div class="ed-panel p-5 text-center text-muted">{{ __('No testimonials yet.') }}</div>
        </div>
    @endforelse
</div>
@endsection
