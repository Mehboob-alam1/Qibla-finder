@extends('layouts.admin')
@section('title', $post->exists ? 'Edit article' : 'New article')
@section('heading', $post->exists ? 'Edit article' : 'New article')

@section('content')
<form method="POST" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}" class="stat-card rounded-3xl p-6 space-y-4 max-w-4xl">
    @csrf
    @if ($post->exists) @method('PUT') @endif
    <label class="block text-sm">Title <input name="title" value="{{ old('title', $post->title) }}" required class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <div class="grid md:grid-cols-2 gap-4">
        <label class="text-sm">Slug <input name="slug" value="{{ old('slug', $post->slug) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
        <label class="text-sm">Locale
            <select name="locale" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
                @foreach (array_keys(config('qibla.locales')) as $code)
                    <option value="{{ $code }}" @selected(old('locale', $post->locale) === $code)>{{ $code }}</option>
                @endforeach
            </select>
        </label>
    </div>
    <label class="block text-sm">Excerpt <input name="excerpt" value="{{ old('excerpt', $post->excerpt) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <label class="block text-sm">Content <textarea name="content" rows="14" required class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-3 font-mono text-sm">{{ old('content', $post->content) }}</textarea></label>
    <label class="block text-sm">SEO title <input name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <label class="block text-sm">SEO description <input name="meta_description" value="{{ old('meta_description', $post->meta_description) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $post->is_published))> Published</label>
    <button class="bg-forest text-cream rounded-full px-6 py-3">Save article</button>
</form>
@endsection
