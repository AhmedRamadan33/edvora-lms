@extends('layouts.panel')
@section('heading', __('Categories'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Categories') }}</h2>
        <p>{{ __('Organize the marketplace catalog.') }}</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">{{ __('Create') }}</a>
</div>

<x-table-toolbar :placeholder="__('Search categories')" />
<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td>{{ $categories->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-semibold">
                                @if($category->icon)<i class="bi {{ $category->icon }} me-1"></i>@endif
                                {{ $category->translation()?->name }}
                            </div>
                        </td>
                        <td>
                            <span class="ed-status is-{{ $category->is_active ? 'active' : 'inactive' }}">
                                {{ $category->is_active ? __('Active') : __('Inactive') }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">{{ __('Edit') }}</a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" data-confirm-delete>
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-primary">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$categories" />
@endsection
