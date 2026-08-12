@extends('layouts.panel')
@section('heading', __('Students'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Students') }}</h2>
        <p>{{ __('Manage student accounts.') }}</p>
    </div>
</div>

<x-table-toolbar :placeholder="__('Search students')" />
<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>{{ $students->firstItem() + $loop->index }}</td>
                        <td class="fw-semibold">{{ $student->name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>
                            <span class="ed-status is-{{ $student->is_active ? 'active' : 'inactive' }}">
                                {{ $student->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <form method="POST" action="{{ route('admin.users.toggle', $student) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary">{{ __('Toggle') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted p-4">{{ __('No students yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$students" />
@endsection
