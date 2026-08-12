@extends('layouts.panel')
@section('heading', __('Users'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Users') }}</h2>
        <p>{{ __('Manage student, instructor, and admin accounts.') }}</p>
    </div>
</div>

<x-table-toolbar :placeholder="__('Search users')">
    @if (request('role'))
        <input type="hidden" name="role" value="{{ request('role') }}">
    @endif
</x-table-toolbar>
<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Roles') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $users->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                        <td>
                            <span class="ed-status is-{{ $user->is_active ? 'active' : 'inactive' }}">
                                {{ $user->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary">{{ __('Toggle') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$users" />
@endsection
