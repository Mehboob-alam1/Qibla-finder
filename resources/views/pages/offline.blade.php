@extends('layouts.app')

@section('title', __('ui.offline_title'))
@section('description', __('ui.offline_body'))

@section('content')
<section class="mx-auto max-w-xl px-4 py-20 text-center">
    <h1 class="font-display text-5xl text-forest">{{ __('ui.offline_title') }}</h1>
    <p class="mt-4 text-forest/70">{{ __('ui.offline_body') }}</p>
    <a class="inline-block mt-8 bg-forest text-cream rounded-full px-6 py-3" href="{{ route('home') }}">{{ __('ui.Find Qibla') }}</a>
</section>
@endsection
