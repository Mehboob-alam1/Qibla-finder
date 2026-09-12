@extends('layouts.app')

@section('title', __('ui.FAQ') . ' — ' . $siteName)
@section('description', __('ui.faq_intro'))

@push('head')
    @include('partials.faq-schema', ['schemaFaqs' => $faqs->flatten()])
@endpush

@section('content')
<div class="mx-auto max-w-3xl px-4 pt-14">
    <h1 class="font-display text-6xl text-forest">{{ __('ui.FAQ') }}</h1>
    <p class="mt-3 text-forest/70">{{ __('ui.faq_intro') }}</p>
</div>

@include('partials.qibla-help', ['helpClass' => 'mt-10'])

<div class="mx-auto max-w-3xl px-4 pb-14">
    @forelse ($faqs as $category => $items)
        <h2 class="mt-12 font-display text-3xl text-gold capitalize">{{ __(match ($category) {
            'qibla' => 'ui.Find Qibla',
            'prayer' => 'ui.Prayer Times',
            'general' => 'ui.How it works',
            default => 'ui.FAQ',
        }) }}</h2>
        <div class="mt-4 space-y-4">
            @foreach ($items as $faq)
                <details class="stat-card rounded-3xl px-6 py-4">
                    <summary class="cursor-pointer font-semibold text-forest">{{ $faq->question }}</summary>
                    <div class="prose-content mt-3 text-forest/70">{!! $faq->answer !!}</div>
                </details>
            @endforeach
        </div>
    @empty
        <p class="mt-8 text-forest/60">{{ __('ui.faq_empty') }}</p>
    @endforelse
    @include('partials.ad', ['type' => 'banner'])
</div>
@endsection
