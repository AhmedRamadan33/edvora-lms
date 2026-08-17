@extends('layouts.panel')
@section('heading', __('Certificates'))
@section('sidebar')
@include('student.partials.nav')
@endsection
@section('content')
<div class="ed-page-head">
    <div>
        <h2>{{ __('Certificates') }}</h2>
        <p>{{ __('Download proof of completion for finished courses.') }}</p>
    </div>
</div>

<div class="ed-table-wrap">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>{{ __('Course') }}</th>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Issued') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($certificates as $certificate)
                    <tr>
                        <td class="fw-semibold">{{ $certificate->course->translation()?->title }}</td>
                        <td>{{ $certificate->code }}</td>
                        <td>{{ $certificate->issued_at?->format('Y-m-d') }}</td>
                        <td class="text-end pe-3">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('student.certificates.download', $certificate) }}">{{ __('Download') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-4 text-muted">{{ __('No certificates yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
