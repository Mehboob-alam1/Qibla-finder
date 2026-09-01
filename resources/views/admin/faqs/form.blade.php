@extends('layouts.admin')
@section('title', $faq->exists ? 'Edit FAQ' : 'New FAQ')
@section('heading', $faq->exists ? 'Edit FAQ' : 'New FAQ')

@section('content')
<form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="stat-card rounded-3xl p-6 space-y-4 max-w-3xl">
    @csrf
    @if ($faq->exists) @method('PUT') @endif
    <label class="block text-sm">Question <input name="question" value="{{ old('question', $faq->question) }}" required class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <label class="block text-sm">Answer <textarea name="answer" rows="8" required class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-3">{{ old('answer', $faq->answer) }}</textarea></label>
    <div class="grid md:grid-cols-3 gap-4">
        <label class="text-sm">Category <input name="category" value="{{ old('category', $faq->category) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
        <label class="text-sm">Locale
            <select name="locale" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
                @foreach (array_keys(config('qibla.locales')) as $code)
                    <option value="{{ $code }}" @selected(old('locale', $faq->locale) === $code)>{{ $code }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm">Order <input type="number" name="sort_order" value="{{ old('sort_order', $faq->sort_order ?? 0) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $faq->is_published))> Published</label>
    <button class="bg-forest text-cream rounded-full px-6 py-3">Save FAQ</button>
</form>
@endsection
