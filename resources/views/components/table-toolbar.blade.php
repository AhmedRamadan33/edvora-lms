@props([
    'placeholder' => __('Search'),
    'query' => request('search'),
    'showReset' => true,
])

<form method="GET" class="ed-table-toolbar mb-3">
    {{ $slot }}
    <div class="input-group ed-table-search">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="search" name="search" value="{{ $query }}" class="form-control"
            placeholder="{{ $placeholder }}" aria-label="{{ __('Search') }}">
        <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>
        @if ($showReset && (request()->has('search') || request()->except('page')))
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
        @endif
    </div>
</form>
