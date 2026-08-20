@extends('layouts.panel')
@section('heading', __('Edit course'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
    @php
        $en = $course->translations->firstWhere('locale', 'en');
        $ar = $course->translations->firstWhere('locale', 'ar');
    @endphp

    <div class="ed-page-head">
        <div>
            <h2>{{ $course->translation()?->title ?: __('Edit course') }}</h2>
            <p>
                <span class="ed-status is-{{ $course->status }}">{{ __status($course->status) }}</span>
                @if ($course->rejection_reason)
                    <span class="text-danger small ms-2">{{ $course->rejection_reason }}</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('instructor.question-bank.index', $course) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-journal-text"></i> {{ __('Question bank') }}
            </a>
            <form method="POST" action="{{ route('instructor.courses.submit', $course) }}">
                @csrf
                <button class="btn btn-success btn-sm">{{ __('Submit for review') }}</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('instructor.courses.update', $course) }}" enctype="multipart/form-data"
        class="ed-panel p-4 mb-4">
        <div class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label">{{ __('Title (EN)') }}</label>
                <input name="title_en" class="form-control" value="{{ $en?->title }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Title (AR)') }}</label>
                <input name="title_ar" class="form-control" value="{{ $ar?->title }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Subtitle (EN)') }}</label>
                <input name="subtitle_en" class="form-control" value="{{ $en?->subtitle }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Subtitle (AR)') }}</label>
                <input name="subtitle_ar" class="form-control" value="{{ $ar?->subtitle }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Description (EN)') }}</label>
                <textarea name="description_en" class="form-control" rows="4">{{ $en?->description }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('Description (AR)') }}</label>
                <textarea name="description_ar" class="form-control" rows="4">{{ $ar?->description }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Category') }}</label>
                <select name="category_id" class="form-select">
                    <option value="">-</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($course->category_id == $category->id)>
                            {{ $category->translation()?->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Level') }}</label>
                <select name="level" class="form-select">
                    @foreach (['beginner', 'intermediate', 'advanced'] as $level)
                        <option value="{{ $level }}" @selected($course->level === $level)>{{ __($level) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Language') }}</label>
                <select name="language" class="form-select">
                    <option value="en" @selected($course->language === 'en')>{{ __('en') }}</option>
                    <option value="ar" @selected($course->language === 'ar')>{{ __('ar') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Price') }}</label>
                <input type="number" step="0.01" name="price" class="form-control" value="{{ $course->price }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Thumbnail') }}</label>
                @if($course->thumbnail)
                    <img src="{{ asset('storage/'.$course->thumbnail) }}" alt="{{ __('Current thumbnail') }}" class="d-block mb-2 rounded" style="width:160px; height:100px; object-fit:cover;">
                @endif
                <input type="file" name="thumbnail" class="form-control">
                @if($course->thumbnail)
                    <div class="form-text">{{ __('Uploading a new file will replace the current thumbnail.') }}</div>
                @endif
            </div>
            <div class="col-12">
                <button class="btn btn-primary">{{ __('Save course') }}</button>
            </div>
        </div>
    </form>

    <div class="ed-panel p-4 mb-4">
        <h2 class="h5 mb-3">{{ __('Add section') }}</h2>
        <form method="POST" action="{{ route('instructor.sections.store', $course) }}" class="d-flex gap-2">
            @csrf
            <input name="title" class="form-control" placeholder="{{ __('Section title') }}" required>
            <button class="btn btn-outline-primary">{{ __('Add') }}</button>
        </form>
    </div>

    @foreach ($course->sections as $section)
        <div class="ed-panel p-4 mb-3">
            <h3 class="h5 mb-3">{{ $section->title }}</h3>

            <ul class="list-group mb-4">
                @forelse($section->lessons as $lesson)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            {{ $lesson->title }}
                            <span class="badge text-bg-light">{{ $lesson->type }}</span>
                            @if ($lesson->is_preview)
                                <span class="badge text-bg-info">{{ __('Preview') }}</span>
                            @endif
                            @if ($lesson->video)
                                · {{ __status($lesson->video->status) }}
                            @endif
                        </span>
                        <span class="d-flex gap-1">
                            @if ($lesson->type === 'video' && $lesson->video)
                                <form method="POST" action="{{ route('instructor.lessons.check-status', [$course, $lesson]) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">{{ __('Check status') }}</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('instructor.lessons.destroy', [$course, $lesson]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                            </form>
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">{{ __('No lessons yet.') }}</li>
                @endforelse
            </ul>

            <form method="POST" action="{{ route('instructor.lessons.store', [$course, $section]) }}"
                enctype="multipart/form-data" class="lesson-form border rounded-3 p-3 bg-light" data-lesson-form
                data-video-credentials-url="{{ route('instructor.videos.credentials', $course) }}"
                data-uploading-label="{{ __('Uploading...') }}"
                data-ready-label="{{ __('Video ready.') }}"
                data-processing-label="{{ __('Upload complete, processing...') }}"
                data-failed-label="{{ __('Upload failed.') }}">
                @csrf
                <h4 class="h6 mb-3">{{ __('Add lesson') }}</h4>

                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">{{ __('Lesson title') }}</label>
                        <input name="title" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Lesson type') }}</label>
                        <select name="type" class="form-select" data-lesson-type>
                            <option value="video">{{ __('Video') }}</option>
                            <option value="article">{{ __('Article') }}</option>
                            <option value="file">{{ __('File') }}</option>
                            <option value="quiz">{{ __('Quiz') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_preview" value="1" class="form-check-input"
                                id="preview{{ $section->id }}">
                            <label class="form-check-label"
                                for="preview{{ $section->id }}">{{ __('Free preview') }}</label>
                        </div>
                    </div>
                </div>

                <div class="mt-3" data-panel="video">
                    <label class="form-label">{{ __('Video file') }}</label>
                    <input type="file" accept="video/*" class="form-control" data-video-input>
                    <div class="form-text">{{ __('The video uploads directly and securely; it never passes through our servers.') }}</div>

                    <div class="progress mt-2 d-none" style="height:0.5rem;" data-video-progress-wrap>
                        <div class="progress-bar" role="progressbar" style="width:0%" data-video-progress></div>
                    </div>
                    <div class="small mt-1" data-video-status></div>

                    <input type="hidden" name="video_id" data-video-id-field>
                </div>

                <div class="mt-3 d-none" data-panel="article">
                    <label class="form-label">{{ __('Article content') }}</label>
                    <textarea name="content" class="form-control" rows="4" data-article-content
                        placeholder="{{ __('Write the lesson content...') }}"></textarea>
                </div>

                <div class="mt-3 d-none" data-panel="file">
                    <label class="form-label">{{ __('Attachment file') }}</label>
                    <input type="file" name="attachment" class="form-control" data-file-input>
                    <div class="form-text">{{ __('PDF, ZIP, images... max 10MB') }}</div>
                </div>

                <div class="mt-3 d-none" data-panel="quiz">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label">{{ __('Quiz title') }}</label>
                            <input name="quiz_title" class="form-control" data-quiz-field>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Pass percent') }}</label>
                            <input type="number" name="pass_percent" class="form-control" value="70"
                                min="1" max="100" data-quiz-field>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>{{ __('Questions') }}</strong>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-question>
                            <i class="bi bi-plus-lg"></i> {{ __('Add question') }}
                        </button>
                    </div>

                    <div data-questions-list></div>
                </div>

                <div class="mt-3">
                    <button class="btn btn-primary btn-sm" data-lesson-submit>{{ __('Add lesson') }}</button>
                </div>
            </form>
        </div>
    @endforeach

    <div class="ed-panel p-4 mb-4">
        <h2 class="h5 mb-3">{{ __('Live Classes') }}</h2>

        <ul class="list-group mb-1">
            @forelse ($liveClasses as $liveClass)
                @php
                    $liveState = $liveClass->computedState();
                    $liveStateClass = ['upcoming' => 'pending', 'live' => 'active', 'ended' => 'inactive', 'failed' => 'failed'][$liveState] ?? 'pending';
                @endphp
                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>
                        <strong>{{ $liveClass->title }}</strong>
                        <span class="badge text-bg-light">{{ $liveClass->provider === 'zoom' ? __('Zoom') : __('Google Meet') }}</span>
                        <span class="ed-status is-{{ $liveStateClass }}">{{ __status($liveState) }}</span>
                        <br>
                        <small class="text-muted">{{ $liveClass->scheduledAtLocal()->format('Y-m-d H:i') }} · {{ $liveClass->duration_minutes }} {{ __('min') }}</small>
                    </span>
                    <span class="d-flex gap-1">
                        @if (! in_array($liveState, ['ended', 'failed'], true))
                            @if ($liveClass->provider === 'zoom' && $liveClass->start_url)
                                <a href="{{ $liveClass->start_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">{{ __('Start') }}</a>
                            @elseif ($liveClass->join_url)
                                <a href="{{ $liveClass->join_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">{{ __('Join') }}</a>
                            @endif
                        @endif
                        <form method="POST" action="{{ route('instructor.live-classes.destroy', $liveClass) }}" data-confirm-delete>
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                        </form>
                    </span>
                </li>
            @empty
                <li class="list-group-item text-muted">{{ __('No live classes scheduled yet.') }}</li>
            @endforelse
        </ul>

        <x-table-pagination :paginator="$liveClasses" />

        @if (empty($connectedLiveProviders))
            <div class="alert alert-warning mb-0">
                {{ __('Connect a Zoom or Google account to schedule live classes.') }}
                <a href="{{ route('instructor.integrations.index') }}">{{ __('Go to Integrations') }}</a>
            </div>
        @else
            <form method="POST" action="{{ route('instructor.live-classes.store', $course) }}" class="border rounded-3 p-3 bg-light">
                @csrf
                <h4 class="h6 mb-3">{{ __('Schedule live class') }}</h4>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">{{ __('Title') }}</label>
                        <input name="title" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Provider') }}</label>
                        <select name="provider" class="form-select" required>
                            @if (in_array('zoom', $connectedLiveProviders))
                                <option value="zoom">{{ __('Zoom') }}</option>
                            @endif
                            @if (in_array('google_meet', $connectedLiveProviders))
                                <option value="google_meet">{{ __('Google Meet') }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Scheduled at') }}</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Duration (minutes)') }}</label>
                        <input type="number" name="duration_minutes" class="form-control" value="60" min="15" max="480" required>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">{{ __('Description') }}</label>
                        <input name="description" class="form-control">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary btn-sm">{{ __('Schedule live class') }}</button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <template id="lesson-question-template">
        <div class="card border mb-3" data-question-item>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong data-question-label>{{ __('Question') }}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                        data-remove-question>{{ __('Remove') }}</button>
                </div>
                <input type="text" class="form-control mb-2" data-q-text placeholder="{{ __('Question text') }}">
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <input type="text" class="form-control" data-q-option placeholder="{{ __('Option') }} A">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" data-q-option placeholder="{{ __('Option') }} B">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" data-q-option
                            placeholder="{{ __('Option') }} C ({{ __('optional') }})">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" data-q-option
                            placeholder="{{ __('Option') }} D ({{ __('optional') }})">
                    </div>
                </div>
                <label class="form-label">{{ __('Correct answer') }}</label>
                <select class="form-select" data-q-correct>
                    <option value="0">A</option>
                    <option value="1">B</option>
                    <option value="2">C</option>
                    <option value="3">D</option>
                </select>
            </div>
        </div>
    </template>
@endsection
