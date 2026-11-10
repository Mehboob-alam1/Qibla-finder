@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)
@section('description', $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->content), 160))

@php
    $styled = $page->hasImportedStyles();
@endphp

@push('head')
    <style id="cms-imported-base">
        .cms-imported{width:100%;min-height:40vh;box-sizing:border-box}
        .cms-imported *,.cms-imported *::before,.cms-imported *::after{box-sizing:border-box}
        .cms-imported img,.cms-imported video,.cms-imported iframe{max-width:100%;height:auto}
        .cms-imported-title{margin:0;padding:1.5rem 1rem 0}
    </style>
    @if ($styled && filled($page->custom_css))
        <style id="cms-imported-css">{!! $page->custom_css !!}</style>
    @endif
@endpush

@section('content')
@if ($styled)
    <div
        class="cms-imported {{ $page->body_class }}"
        @if (filled($page->body_style)) style="{{ $page->body_style }}" @endif
    >
        @if ($page->show_page_title ?? false)
            <h1 class="cms-imported-title">{{ $page->title }}</h1>
        @endif
        {!! $page->content !!}
    </div>
@else
    <article class="mx-auto max-w-3xl px-4 py-14">
        @if ($page->show_page_title ?? true)
            <h1 class="font-display text-6xl text-forest">{{ $page->title }}</h1>
        @endif
        <div class="prose-content {{ ($page->show_page_title ?? true) ? 'mt-8' : '' }} text-lg">{!! $page->content !!}</div>
    </article>
@endif
@endsection
