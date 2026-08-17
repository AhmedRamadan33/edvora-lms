@php
    $typeIcons = [
        'video' => 'bi-play-circle',
        'article' => 'bi-file-text',
        'file' => 'bi-download',
        'quiz' => 'bi-patch-question',
    ];
    $percent = (float) ($enrollment->progress_percent ?? 0);
@endphp

<aside class="learn-sidebar">
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-semibold">{{ __('Your progress') }}</span>
            <span class="small text-muted">{{ (int) $percent }}%</span>
        </div>
        <div class="progress" role="progressbar" style="height:0.5rem;border-radius:999px">
            <div class="progress-bar" style="width: {{ $percent }}%; background:var(--ed-accent)"></div>
        </div>
    </div>

    @foreach($course->sections as $section)
        <div class="mb-3">
            <div class="small text-uppercase text-muted fw-bold mb-2">{{ $section->title }}</div>
            @foreach($section->lessons as $lesson)
                @php($done = $progress->get($lesson->id)?->is_completed)
                <a href="{{ route('learn.lesson', [$course, $lesson]) }}"
                    class="learn-lesson-link {{ isset($activeLesson) && $activeLesson?->id === $lesson->id ? 'is-active' : '' }}">
                    <span>
                        <i class="bi {{ $typeIcons[$lesson->type] ?? 'bi-circle' }} me-2"></i>{{ $lesson->title }}
                    </span>
                    @if($done)
                        <i class="bi bi-check-circle-fill text-success"></i>
                    @else
                        <span class="small text-muted">{{ __(ucfirst($lesson->type)) }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endforeach
</aside>
