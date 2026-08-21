<form method="POST" action="{{ $actionUrl }}" class="ed-panel p-4" data-announcement-form
    data-confirm-message="{{ __('This will send an email and a notification to every selected student. Continue?') }}">
    @csrf
    <h3 class="h6 mb-3">{{ __('Send announcement') }}</h3>

    <div class="mb-3">
        <label class="form-label">{{ __('Subject') }}</label>
        <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror"
            value="{{ old('subject') }}" maxlength="150" required>
        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label class="form-label">{{ __('Message') }}</label>
        <textarea name="body" rows="5" class="form-control @error('body') is-invalid @enderror"
            maxlength="5000" required>{{ old('body') }}</textarea>
        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label class="form-label d-block">{{ __('Send to') }}</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="audience" id="audience-all" value="all"
                data-announcement-audience {{ old('audience', 'all') === 'all' ? 'checked' : '' }}>
            <label class="form-check-label" for="audience-all">{{ __('All students') }}</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="audience" id="audience-selected" value="selected"
                data-announcement-audience {{ old('audience') === 'selected' ? 'checked' : '' }}>
            <label class="form-check-label" for="audience-selected">{{ __('Specific students') }}</label>
        </div>
        @error('audience')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3 d-none" data-announcement-students>
        <label class="form-label">{{ __('Students') }}</label>
        <select name="student_ids[]" class="form-select @error('student_ids') is-invalid @enderror" multiple>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected(in_array($student->id, old('student_ids', [])))>
                    {{ $student->name }} ({{ $student->email }})
                </option>
            @endforeach
        </select>
        @error('student_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <button class="btn btn-primary">{{ __('Send') }}</button>
</form>
