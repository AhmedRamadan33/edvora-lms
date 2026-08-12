@extends('layouts.app')
@php($t = $course->translation())
@section('title', $t?->title)
@section('content')
<section class="course-hero">
    <div class="course-hero__bg" style="background-image:url('{{ $cover }}')"></div>
    <div class="course-hero__content">
        <div class="small mb-2 opacity-75">{{ $course->category?->translation()?->name }} · {{ ucfirst($course->level) }}</div>
        <h1 class="display-font mb-2" style="font-size:clamp(1.8rem,4vw,2.8rem)">{{ $t?->title }}</h1>
        <p class="mb-3 opacity-85">{{ $t?->subtitle }}</p>
        <div class="d-flex flex-wrap gap-3 align-items-center">
            <a href="{{ route('instructors.show', $course->instructor) }}" class="text-white">{{ $course->instructor->name }}</a>
            <span>{{ number_format($course->avg_rating, 1) }} ★ ({{ $course->reviews_count }})</span>
            <span>{{ $course->students_count }} {{ __('students') }}</span>
        </div>
    </div>
</section>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="ed-panel p-4 mb-4">
            <h2 class="h4 mb-3">{{ __('About this course') }}</h2>
            <div class="text-secondary">{!! nl2br(e($t?->description)) !!}</div>
        </div>

        <div class="ed-panel p-4 mb-4">
            <h2 class="h4 mb-3">{{ __('Curriculum') }}</h2>
            @foreach($course->sections as $section)
                <div class="mb-3">
                    <div class="fw-semibold mb-2">{{ $section->title }}</div>
                    <ul class="list-group list-group-flush rounded-3 border">
                        @foreach($section->lessons as $lesson)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3">
                                <span>
                                    <i class="bi @if($lesson->type==='video') bi-play-circle @elseif($lesson->type==='quiz') bi-ui-checks-grid @else bi-file-text @endif me-1"></i>
                                    {{ $lesson->title }}
                                </span>
                                <span class="text-muted small">
                                    {{ $lesson->type }}
                                    @if($lesson->is_preview) · {{ __('Preview') }} @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="ed-panel p-4">
            <h2 class="h4 mb-3">{{ __('Reviews') }}</h2>
            @forelse($course->reviews as $review)
                <div class="border rounded-3 p-3 mb-2 bg-white">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $review->user->name }}</strong>
                        <span>{{ $review->rating }}★</span>
                    </div>
                    <div class="text-secondary mt-1">{{ $review->comment }}</div>
                </div>
            @empty
                <p class="text-muted mb-0">{{ __('No reviews yet.') }}</p>
            @endforelse

            @auth
                @if($enrolled)
                    <form method="POST" action="{{ route('reviews.store', $course) }}" class="mt-4 pt-3 border-top">
                        @csrf
                        <h3 class="h6">{{ __('Write a review') }}</h3>
                        <select name="rating" class="form-select mb-2" required>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} ★</option>
                            @endfor
                        </select>
                        <textarea name="comment" class="form-control mb-2" rows="3" placeholder="{{ __('Share your experience') }}"></textarea>
                        <button class="btn btn-primary">{{ __('Submit review') }}</button>
                    </form>
                @endif
            @endauth
        </div>
    </div>

    <div class="col-lg-4">
        <div class="buy-box">
            <div class="price mb-2">{{ number_format($course->price, 2) }} <small class="fs-6">{{ $course->currency }}</small></div>
            <p class="text-muted small mb-3">{{ __('One-time purchase. Lifetime access to this course.') }}</p>

            @auth
                @if($enrolled)
                    <a href="{{ route('learn.course', $course) }}" class="btn btn-success w-100 mb-2">{{ __('Continue learning') }}</a>
                @else
                    <form method="POST" action="{{ route('cart.store', $course) }}" class="mb-2">
                        @csrf
                        <button class="btn btn-primary w-100">{{ __('Add to cart') }}</button>
                    </form>
                    <form method="POST" action="{{ route('wishlist.store', $course) }}">
                        @csrf
                        <button class="btn btn-outline-primary w-100">{{ __('Add to wishlist') }}</button>
                    </form>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-primary w-100">{{ __('Log in to buy') }}</a>
            @endauth
        </div>
    </div>
</div>
@endsection
