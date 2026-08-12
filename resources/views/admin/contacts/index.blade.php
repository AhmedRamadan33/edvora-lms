@extends('layouts.panel')
@section('heading', __('Contact messages'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Contact messages') }}</h2>
        <p>{{ __('Messages submitted from the public contact form.') }}</p>
    </div>
</div>

<x-table-toolbar :placeholder="__('Search messages')" />
<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Subject') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('When') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $message)
                    <tr>
                        <td>{{ $messages->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold">{{ $message->name }}</td>
                        <td>{{ $message->email }}</td>
                        <td>{{ $message->subject ?: '—' }}</td>
                        <td><span class="ed-status is-{{ $message->status === 'new' ? 'pending' : 'published' }}">{{ __status($message->status) }}</span></td>
                        <td>{{ $message->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end pe-3">
                            <a href="{{ route('admin.contacts.show', $message) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-4 text-muted">{{ __('No messages yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$messages" />
@endsection
