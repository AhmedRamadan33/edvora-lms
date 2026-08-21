@extends('layouts.panel')
@section('heading', __('Announcements'))
@section('sidebar')@include('instructor.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Announcements') }}</h2>
        <p>{{ __('Send an email and in-app notification to your students.') }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        @include('partials.announcement-form', ['actionUrl' => route('instructor.announcements.store'), 'students' => $students])
    </div>
    <div class="col-lg-7">
        <x-table-toolbar :placeholder="__('Search announcements')" />
        <div class="ed-table-wrap">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Subject') }}</th>
                            <th>{{ __('Recipients') }}</th>
                            <th>{{ __('Sent') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $announcement)
                            <tr>
                                <td class="fw-semibold">{{ $announcement->subject }}</td>
                                <td>{{ $announcement->recipients_count }}</td>
                                <td title="{{ $announcement->created_at }}">{{ $announcement->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="p-4 text-muted">{{ __('No announcements sent yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <x-table-pagination :paginator="$announcements" />
    </div>
</div>
@endsection
