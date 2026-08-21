@extends('layouts.panel')
@section('heading', __('Instructors'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Instructor applications') }}</h2>
        <p>{{ __('Approve sellers before they publish on the marketplace.') }}</p>
    </div>
    <div>
        <a href="{{ route('admin.instructors.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> {{ __('Add instructor') }}
        </a>
    </div>
</div>

<x-table-toolbar :placeholder="__('Search instructors')" />
<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Headline') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($profiles as $profile)
                    <tr>
                        <td>{{ $profiles->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $profile->user->name }}</div>
                            <div class="small text-muted">{{ $profile->user->email }}</div>
                        </td>
                        <td>{{ $profile->headline ?: '—' }}</td>
                        <td><span class="ed-status is-{{ $profile->status }}">{{ __status($profile->status) }}</span></td>
                        <td class="text-end pe-3">
                            <form method="POST" action="{{ route('admin.instructors.approve', $profile) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success">{{ __('Approve') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.instructors.reject', $profile) }}" class="d-inline-flex gap-1 mt-1 mt-xl-0">
                                @csrf
                                <input name="rejection_reason" class="form-control form-control-sm" placeholder="{{ __('Reason') }}" required style="min-width:140px">
                                <button class="btn btn-sm btn-outline-primary">{{ __('Reject') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$profiles" />
@endsection
