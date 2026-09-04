@extends('layouts.app')

@section('title', __('ui.Contact') . ' — ' . $siteName)
@section('description', __('ui.contact_meta'))

@section('content')
<div class="mx-auto max-w-3xl px-4 py-14">
    <h1 class="font-display text-6xl text-forest">{{ __('ui.Contact') }}</h1>
    <p class="mt-3 text-forest/70">{{ __('ui.contact_intro', ['email' => $email]) }}</p>

    @if (session('status'))
        <div class="mt-6 rounded-2xl bg-moss/10 text-forest px-4 py-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('contact.store') }}" class="stat-card rounded-3xl p-6 mt-8 space-y-4">
        @csrf
        <label class="block text-sm">{{ __('ui.Name') }}
            <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-3">
            @error('name') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
        </label>
        <label class="block text-sm">{{ __('ui.Email') }}
            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-3">
            @error('email') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
        </label>
        <label class="block text-sm">{{ __('ui.Subject') }}
            <input name="subject" value="{{ old('subject') }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-3">
        </label>
        <label class="block text-sm">{{ __('ui.Message') }}
            <textarea name="message" rows="6" required class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-3">{{ old('message') }}</textarea>
            @error('message') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
        </label>
        <button class="w-full bg-forest text-cream rounded-full py-3 font-semibold">{{ __('ui.Send message') }}</button>
    </form>
    @include('partials.ad', ['type' => 'banner'])
</div>
@endsection
