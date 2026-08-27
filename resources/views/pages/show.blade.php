@extends('layouts.app')
@php($t = $page->translation())
@section('title', $t?->title.' - '.\App\Services\SettingService::platformName())
@section('description', \Illuminate\Support\Str::limit(strip_tags($t?->body ?: ''), 160))
@section('content')
<article class="ed-panel p-4 p-lg-5" style="max-width:860px;margin:0 auto">
    <h1 class="mb-3" style="font-size:clamp(2rem,4vw,2.8rem)">{{ $t?->title }}</h1>
    <div class="text-secondary" style="font-size:1.05rem;line-height:1.8">{!! nl2br(e($t?->body)) !!}</div>
</article>
@endsection
