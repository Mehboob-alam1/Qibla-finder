@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)
@section('description', $page->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($page->content), 160))

@section('content')
<article class="mx-auto max-w-3xl px-4 py-14">
    @if ($page->show_page_title ?? true)
        <h1 class="font-display text-6xl text-forest">{{ $page->title }}</h1>
    @endif
    <div class="prose-content {{ ($page->show_page_title ?? true) ? 'mt-8' : '' }} text-lg">{!! $page->content !!}</div>
</article>
@endsection
