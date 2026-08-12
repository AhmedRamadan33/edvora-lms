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

<x-table-toolbar :placeholder="__('Search activity')" />
<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('When') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Action') }}</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $logs->firstItem() + $loop->index }}</td>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->user?->name ?? '—' }}</td>
                        <td class="fw-semibold">{{ $log->action }}</td>
                        <td class="text-muted">{{ $log->ip_address }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$logs" />
@endsection
