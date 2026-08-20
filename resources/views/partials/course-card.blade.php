@php($t = $course->translation())
<a href="{{ route('courses.show', $course->slug) }}" class="course-tile text-decoration-none text-reset">
    <div class="course-tile__media">
        <img src="{{ $course->thumbnail ? asset('storage/' . $course->thumbnail) : asset('images/course_thumbnail.png') }}"
            alt="{{ $t?->title }}" loading="lazy">
    </div>
    <div class="course-tile__body">
        <div class="course-tile__meta">
            {{ $course->instructor?->name }}
            @if ($course->level)
                · {{ ucfirst($course->level) }}
            @endif
        </div>
        <h3 class="course-tile__title">{{ $t?->title }}</h3>
        <p class="course-tile__excerpt">{{ Str::limit($t?->subtitle ?: $t?->description, 78) }}</p>
        <div class="course-tile__footer">
            <div class="course-tile__price">{{ money($course->price) }}</div>
            <span class="small text-muted">
                @if ($course->reviews_count)
                    {{ number_format($course->avg_rating, 1) }} ★
                @else
                    {{ __('New') }}
                @endif
            </span>
        </div>
    </div>
</a>
