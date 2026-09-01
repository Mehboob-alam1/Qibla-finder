@extends('layouts.app')

@section('title', $post->meta_title ?: $post->title)
@section('description', $post->meta_description ?: $post->excerpt)

@section('content')
<article class="mx-auto max-w-3xl px-4 py-14">
    <p class="text-gold uppercase tracking-[0.25em] text-xs">{{ optional($post->published_at)->format('F j, Y') }}</p>
    <h1 class="font-display text-5xl md:text-6xl text-forest mt-3">{{ $post->title }}</h1>
    <div class="prose-content mt-8 text-lg">{!! $post->content !!}</div>
</article>
@if ($related->isNotEmpty())
<section class="mx-auto max-w-6xl px-4 pb-16">
    <h2 class="font-display text-4xl text-forest mb-6">{{ __('ui.Continue reading') }}</h2>
    <div class="grid md:grid-cols-3 gap-5">
        @foreach ($related as $item)
            <a href="{{ route('blog.show', $item->slug) }}" class="stat-card rounded-3xl p-6 block">
                <h3 class="font-display text-2xl text-forest">{{ $item->title }}</h3>
                <p class="mt-2 text-forest/70">{{ $item->excerpt }}</p>
            </a>
        @endforeach
    </div>
</section>
@endif
@endsection
