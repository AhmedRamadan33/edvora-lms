@php
    $isEdit = isset($question) && $question !== null;
    $action = $isEdit
        ? route('instructor.question-bank.update', [$course, $question])
        : route('instructor.question-bank.store', $course);
    $choices = $isEdit ? $question->choices : collect();
    $matches = $isEdit ? $question->matches : collect();
    $trueAnswer = $isEdit ? ($question->choices->firstWhere('text', 'True')?->is_correct ? 'true' : 'false') : 'true';
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="bank-question-form border rounded-3 p-3"
    data-question-form>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">{{ __('Question text') }}</label>
            <textarea name="question" class="form-control" rows="2" required>{{ $question->question ?? '' }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('Question type') }}</label>
            <select name="type" class="form-select" data-question-type>
                <option value="mcq_single" data-hint="{{ __('Add choices below and mark the one correct answer.') }}" @selected(($question->type ?? '') === 'mcq_single')>{{ __('Multiple choice') }}</option>
                <option value="true_false" data-hint="{{ __('Pick whether the statement is true or false.') }}" @selected(($question->type ?? '') === 'true_false')>{{ __('True / False') }}</option>
                <option value="matching" data-hint="{{ __('Add pairs of items the student must match together.') }}" @selected(($question->type ?? '') === 'matching')>{{ __('Matching') }}</option>
                <option value="fill_blank" data-hint="{{ __('No fixed answer - graded manually by the instructor.') }}" @selected(($question->type ?? '') === 'fill_blank')>{{ __('Fill in the blank') }}</option>
                <option value="essay" data-hint="{{ __('No fixed answer - graded manually by the instructor.') }}" @selected(($question->type ?? '') === 'essay')>{{ __('Essay') }}</option>
            </select>
            <div class="form-text" data-question-type-hint></div>
        </div>

        <div class="col-md-4">
            <label class="form-label">{{ __('Subject') }}</label>
            <select name="subject_id" class="form-select">
                <option value="">{{ __('No subject') }}</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(($question->subject_id ?? '') == $subject->id)>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
            @if ($subjects->isEmpty())
                <div class="form-text">
                    {{ __('No subjects yet.') }}
                    <a href="{{ route('instructor.subjects.create', ['course_id' => $course->id]) }}">{{ __('Add one') }}</a>
                </div>
            @endif
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ __('Difficulty') }}</label>
            <select name="difficulty" class="form-select">
                @foreach (['easy', 'medium', 'hard'] as $level)
                    <option value="{{ $level }}" @selected(($question->difficulty ?? 'medium') === $level)>{{ __($level) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ __('Points') }}</label>
            <input type="number" name="points" class="form-control" min="1" max="100" value="{{ $question->points ?? 1 }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">{{ __('Image') }} ({{ __('optional') }})</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            @if ($isEdit && $question->image)
                <img src="{{ asset('storage/' . $question->image) }}" alt="" class="mt-2 rounded" style="max-height:60px;">
            @endif
        </div>

        {{-- Panel: Multiple choice --}}
        <div class="col-12 d-none" data-panel="mcq_single">
            <label class="form-label d-block">{{ __('Options') }}</label>
            <div data-choices-list>
                @foreach ($choices as $choice)
                    <div class="row g-2 align-items-center mb-2" data-choice-row>
                        <div class="col-auto">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" data-choice-correct value="1"
                                    id="choice-correct-{{ $formId }}-{{ $loop->index }}" @checked($choice->is_correct)>
                                <label class="form-check-label" for="choice-correct-{{ $formId }}-{{ $loop->index }}">{{ __('Correct') }}</label>
                            </div>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control" data-choice-text value="{{ $choice->text }}"
                                placeholder="{{ __('Option text') }}">
                        </div>
                        <div class="col-auto">
                            <input type="file" class="form-control" data-choice-image accept="image/*">
                            @if ($choice->image)
                                <img src="{{ asset('storage/'.$choice->image) }}" alt="" class="mt-1 rounded d-block" style="max-height:40px;">
                            @endif
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-danger btn-sm" data-remove-choice>&times;</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-choice>
                <i class="bi bi-plus-lg"></i> {{ __('Add Option') }}
            </button>
        </div>

        {{-- Panel: True / False --}}
        <div class="col-12 d-none" data-panel="true_false">
            <div class="card border-primary-subtle">
                <div class="card-body">
                    <strong class="d-block mb-2">{{ __('Correct answer') }}</strong>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="true_false_answer" value="true"
                            id="tf-true-{{ $formId }}" @checked($trueAnswer === 'true')>
                        <label class="form-check-label" for="tf-true-{{ $formId }}">{{ __('True') }}</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="true_false_answer" value="false"
                            id="tf-false-{{ $formId }}" @checked($trueAnswer === 'false')>
                        <label class="form-check-label" for="tf-false-{{ $formId }}">{{ __('False') }}</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel: Fill in the blank (no fixed answer - graded manually) --}}
        <div class="col-12 d-none" data-panel="fill_blank">
            <div class="alert alert-secondary mb-0">
                {{ __('Fill in the blank questions have no fixed answer here - the instructor grades the student\'s response manually.') }}
            </div>
        </div>

        {{-- Panel: Matching --}}
        <div class="col-12 d-none" data-panel="matching">
            <div class="card border-primary-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>{{ __('Matching pairs') }}</strong>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-match>
                            <i class="bi bi-plus-lg"></i> {{ __('Add pair') }}
                        </button>
                    </div>
                    <div data-matches-list>
                        @foreach ($matches as $match)
                            <div class="row g-2 mb-2" data-match-row>
                                <div class="col-5">
                                    <input class="form-control" data-match-prompt value="{{ $match->prompt_text }}"
                                        placeholder="{{ __('Key') }}">
                                </div>
                                <div class="col-5">
                                    <input class="form-control" data-match-answer value="{{ $match->match_text }}"
                                        placeholder="{{ __('Value') }}">
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-outline-danger w-100" data-remove-match>&times;</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Panel: Essay (no extra structured fields, just a note) --}}
        <div class="col-12 d-none" data-panel="essay">
            <div class="alert alert-secondary mb-0">
                {{ __('Essay questions have no fixed answer - the instructor grades the student\'s written response manually.') }}
            </div>
        </div>

        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check mb-2">
                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                    id="active-{{ $formId }}" @checked($question->is_active ?? true)>
                <label class="form-check-label" for="active-{{ $formId }}">{{ __('Active') }}</label>
            </div>
        </div>

        <div class="col-12">
            <button class="btn btn-primary btn-sm">{{ $isEdit ? __('Save changes') : __('Add question') }}</button>
        </div>
    </div>
</form>
