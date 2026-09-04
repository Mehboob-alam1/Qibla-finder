@extends('layouts.app')

@section('title', __('ui.Guides'))
@section('description', __('ui.guides_intro'))

@section('content')
<div class="mx-auto max-w-6xl px-4 py-14">
    <p class="text-gold uppercase tracking-[0.25em] text-xs">{{ __('ui.guides_kicker') }}</p>
    <h1 class="font-display text-6xl text-forest mt-2">{{ __('ui.Guides') }}</h1>
    <p class="mt-3 max-w-2xl text-forest/70">{{ __('ui.guides_intro') }}</p>

    @include('partials.ad', ['type' => 'native'])

    <div class="grid md:grid-cols-3 gap-5 mt-10">
        @forelse ($posts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="stat-card rounded-3xl p-6 block hover:-translate-y-1 transition">
                <p class="text-xs text-gold uppercase tracking-widest">{{ optional($post->published_at)->format('M j, Y') }}</p>
                <h2 class="font-display text-3xl mt-2 text-forest">{{ $post->title }}</h2>
                <p class="mt-3 text-forest/70">{{ $post->excerpt }}</p>
            </a>
        @empty
            <p class="text-forest/60">{{ __('ui.guides_empty') }}</p>
        @endforelse
    </div>
    <div class="mt-10">{{ $posts->links() }}</div>
</div>
@endsection
