@extends('layouts.app')

@section('title', __('ui.FAQ') . ' — ' . $siteName)
@section('description', __('ui.faq_intro'))

@section('content')
<div class="mx-auto max-w-3xl px-4 py-14">
    <h1 class="font-display text-6xl text-forest">{{ __('ui.FAQ') }}</h1>
    <p class="mt-3 text-forest/70">{{ __('ui.faq_intro') }}</p>
    @forelse ($faqs as $category => $items)
        <h2 class="mt-12 font-display text-3xl text-gold capitalize">{{ $category }}</h2>
        <div class="mt-4 space-y-4">
            @foreach ($items as $faq)
                <details class="stat-card rounded-3xl px-6 py-4">
                    <summary class="cursor-pointer font-semibold text-forest">{{ $faq->question }}</summary>
                    <p class="mt-3 text-forest/70 leading-relaxed">{{ $faq->answer }}</p>
                </details>
            @endforeach
        </div>
    @empty
        <p class="mt-8 text-forest/60">{{ __('ui.faq_empty') }}</p>
    @endforelse
</div>
@endsection
