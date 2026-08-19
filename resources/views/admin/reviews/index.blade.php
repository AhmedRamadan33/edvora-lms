@extends('layouts.panel')
@section('heading', __('Reviews'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Reviews') }}</h2>
        <p>{{ __('Moderate student reviews across all courses.') }}</p>
    </div>
</div>

<x-table-toolbar :placeholder="__('Search by student name or email')">
    <select name="course_id" class="form-select w-auto">
        <option value="">{{ __('All courses') }}</option>
        @foreach ($courses as $course)
            <option value="{{ $course->id }}" @selected(request('course_id') == $course->id)>{{ $course->translation()?->title }}</option>
        @endforeach
    </select>
    <select name="status" class="form-select w-auto">
        <option value="">{{ __('All statuses') }}</option>
        <option value="approved" @selected(request('status') === 'approved')>{{ __('Approved') }}</option>
        <option value="rejected" @selected(request('status') === 'rejected')>{{ __('Rejected') }}</option>
    </select>
    <select name="rating" class="form-select w-auto">
        <option value="">{{ __('All ratings') }}</option>
        @for($i = 5; $i >= 1; $i--)
            <option value="{{ $i }}" @selected((int) request('rating') === $i)>{{ $i }} ★</option>
        @endfor
    </select>
</x-table-toolbar>

<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Student') }}</th>
                    <th>{{ __('Course') }}</th>
                    <th>{{ __('Rating') }}</th>
                    <th>{{ __('Comment') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr>
                        <td>{{ $reviews->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $review->user->name }}</div>
                            <div class="text-muted small">{{ $review->user->email }}</div>
                        </td>
                        <td>{{ $review->course->translation()?->title }}</td>
                        <td>{{ str_repeat('★', $review->rating) }}</td>
                        <td class="text-truncate" style="max-width:220px;">{{ $review->comment }}</td>
                        <td>
                            <span class="badge text-bg-{{ $review->status === 'approved' ? 'success' : 'danger' }}">
                                {{ $review->status === 'approved' ? __('Approved') : __('Rejected') }}
                            </span>
                            @if($review->isRejected() && $review->admin_note)
                                <div class="text-muted small mt-1">{{ $review->admin_note }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-column gap-1 align-items-end">
                                @if($review->isRejected())
                                    <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">{{ __('Approve') }}</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="d-flex gap-1">
                                        @csrf
                                        <input name="admin_note" class="form-control form-control-sm" required placeholder="{{ __('Reason') }}">
                                        <button class="btn btn-sm btn-outline-danger text-nowrap">{{ __('Reject') }}</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" data-confirm-delete>
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-secondary">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted p-4">{{ __('No reviews yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<x-table-pagination :paginator="$reviews" />
@endsection
