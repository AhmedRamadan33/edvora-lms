@extends('layouts.panel')
@section('heading', __('Reports'))
@section('sidebar')@include('admin.partials.nav')@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Marketplace reports') }}</h2>
        <p>{{ __('Track commissions, growth, and top-performing courses.') }}</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card h-100"><div class="card-body">
            <div class="text-muted">{{ __('Platform commission') }}</div>
            <div class="fs-3 fw-bold mt-1">{{ number_format($platformCommission, 2) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card h-100"><div class="card-body">
            <div class="text-muted">{{ __('New students (30d)') }}</div>
            <div class="fs-3 fw-bold mt-1">{{ $newStudents }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card h-100"><div class="card-body">
            <div class="text-muted">{{ __('Published courses') }}</div>
            <div class="fs-3 fw-bold mt-1">{{ $publishedCourses }}</div>
        </div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="ed-panel p-4 h-100">
            <h3 class="h5 mb-3">{{ __('Sales by day') }}</h3>
            <ul class="list-group list-group-flush">
                @forelse($salesByDay as $row)
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>{{ $row->day }}</span>
                        <strong>{{ number_format($row->total, 2) }}</strong>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-muted">{{ __('No sales data yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ed-panel p-4 h-100">
            <h3 class="h5 mb-3">{{ __('Top courses') }}</h3>
            <ul class="list-group list-group-flush">
                @forelse($topCourses as $row)
                    <li class="list-group-item d-flex justify-content-between px-0 gap-3">
                        <span>{{ $row->course?->translation()?->title }}</span>
                        <strong class="text-nowrap">{{ $row->sales }} / {{ number_format($row->revenue, 2) }}</strong>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-muted">{{ __('No course sales yet.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
