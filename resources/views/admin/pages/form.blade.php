@extends('layouts.admin')
@section('title', $page->exists ? 'Edit page' : 'New page')
@section('heading', $page->exists ? 'Edit page' : 'New page')

@section('content')
<form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="stat-card rounded-3xl p-6 space-y-4 max-w-4xl">
    @csrf
    @if ($page->exists) @method('PUT') @endif
    <label class="block text-sm">Title <input name="title" value="{{ old('title', $page->title) }}" required class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <div class="grid md:grid-cols-3 gap-4">
        <label class="text-sm">Slug <input name="slug" value="{{ old('slug', $page->slug) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
        <label class="text-sm">Locale
            <select name="locale" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
                @foreach (array_keys(config('qibla.locales')) as $code)
                    <option value="{{ $code }}" @selected(old('locale', $page->locale) === $code)>{{ $code }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm">Sort <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order ?? 0) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    </div>
    @include('admin.partials.editor', ['name' => 'content', 'label' => 'Page content', 'value' => $page->content, 'required' => true])
    <label class="block text-sm">SEO title <input name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <label class="block text-sm">SEO description <input name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))> Published</label>
    <button class="bg-forest text-cream rounded-full px-6 py-3">Save page</button>
</form>
@endsection
