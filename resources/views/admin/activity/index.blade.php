@extends('layouts.panel')
@section('heading', __('Activity log'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Activity log') }}</h2>
        <p>{{ __('Audit key marketplace actions across the platform.') }}</p>
    </div>
</div>

<x-table-toolbar :placeholder="__('Search activity')">
    <div class="d-flex flex-wrap gap-2 mb-2">
        <select name="action" class="form-select" style="max-width:220px">
            <option value="">{{ __('All events') }}</option>
            @foreach($actionOptions as $group => $actions)
                <optgroup label="{{ $group }}">
                    @foreach($actions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <input type="text" name="user" value="{{ request('user') }}" class="form-control" placeholder="{{ __('Filter by user') }}" style="max-width:200px">
        <input type="date" name="from" value="{{ request('from') }}" class="form-control" style="max-width:170px" aria-label="{{ __('From date') }}">
        <input type="date" name="to" value="{{ request('to') }}" class="form-control" style="max-width:170px" aria-label="{{ __('To date') }}">
    </div>
</x-table-toolbar>

<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('When') }}</th>
                    <th>{{ __('Message') }}</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $logs->firstItem() + $loop->index }}</td>
                        <td title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</td>
                        <td>{{ $log->description() }}</td>
                        <td class="text-muted">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-muted">{{ __('No activity found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$logs" />
@endsection
