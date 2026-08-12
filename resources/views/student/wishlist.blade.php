@extends('layouts.app')
@section('title', __('Wishlist'))
@section('content')
<div class="ed-page-head">
    <div>
        <h1 class="mb-1" style="font-size:clamp(1.8rem,3vw,2.4rem)">{{ __('Wishlist') }}</h1>
        <p class="text-muted mb-0">{{ __('Courses you saved for later.') }}</p>
    </div>
</div>

<div class="row g-4">
    @forelse($items as $item)
        <div class="col-md-6 col-xl-3">
            @include('partials.course-card', ['course' => $item->course])
            <form method="POST" action="{{ route('wishlist.destroy', $item->course) }}" class="mt-2">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-primary w-100">{{ __('Remove') }}</button>
            </form>
        </div>
    @empty
        <div class="col-12">
            <div class="ed-panel p-5 text-center">
                <h2 class="h5">{{ __('Wishlist is empty.') }}</h2>
                <a href="{{ route('courses.index') }}" class="btn btn-primary mt-2">{{ __('Browse catalog') }}</a>
            </div>
        </div>
    @endforelse
</div>
@endsection
