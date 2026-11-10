@extends('layouts.admin')
@section('title', $page->exists ? 'Edit page' : 'New page')
@section('heading', $page->exists ? 'Edit page' : 'New page')

@section('content')
<form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="stat-card rounded-3xl p-6 space-y-4 max-w-4xl" data-page-form>
    @csrf
    @if ($page->exists) @method('PUT') @endif

    @unless ($page->exists)
        <div class="rounded-2xl border border-gold/40 bg-gold/10 px-4 py-3 text-sm">
            Prefer uploading a full HTML file?
            <a href="{{ route('admin.pages.import') }}" class="font-semibold text-forest underline">Import HTML file</a>
            — it publishes immediately and opens the live page.
        </div>
    @endunless

    <label class="block text-sm">Title <input name="title" data-field="title" value="{{ old('title', $page->title) }}" required class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <fieldset class="rounded-2xl border border-forest/15 p-4 space-y-3" data-url-style>
        <legend class="px-1 text-sm font-medium">Public URL</legend>
        <p class="text-sm text-forest/60">Published pages are live on the site. Prefer <strong>Prefixed</strong> (<code>/p/…</code>) for CMS content. Flat URLs cannot clash with built-in routes.</p>
        <label class="flex items-start gap-3 text-sm">
            <input type="radio" name="url_style" value="flat" class="mt-1" @checked(old('url_style', $page->url_style ?: 'prefixed') === 'flat')>
            <span>
                <strong>Flat</strong> — <code>/{{ old('slug', $page->slug) ?: 'your-page' }}</code>
            </span>
        </label>
        <label class="flex items-start gap-3 text-sm">
            <input type="radio" name="url_style" value="prefixed" class="mt-1" @checked(old('url_style', $page->url_style ?: 'prefixed') === 'prefixed')>
            <span>
                <strong>Prefixed</strong> — <code>/p/{{ old('slug', $page->slug) ?: 'your-page' }}</code>
            </span>
        </label>
        <p class="text-sm">Preview: <code class="text-gold" data-url-preview>{{ $page->exists ? $page->publicPath() : '/p/your-page' }}</code></p>
    </fieldset>
    <div class="grid md:grid-cols-3 gap-4">
        <label class="text-sm">Slug <input name="slug" data-url-slug data-field="slug" value="{{ old('slug', $page->slug) }}" placeholder="about" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
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
    <label class="block text-sm">Imported CSS
        <span class="block mt-1 text-forest/50 font-normal">Pulled from the HTML file’s <code>&lt;style&gt;</code> tags (and https stylesheets). Scoped so it does not restyle the site header/footer.</span>
        <textarea name="custom_css" data-field="custom_css" rows="8" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-3 font-mono text-xs">{{ old('custom_css', $page->custom_css) }}</textarea>
    </label>
    <div class="grid md:grid-cols-2 gap-4">
        <label class="text-sm">Body class <input name="body_class" data-field="body_class" value="{{ old('body_class', $page->body_class) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5" placeholder="from &lt;body class&gt;"></label>
        <label class="text-sm">Body style <input name="body_style" data-field="body_style" value="{{ old('body_style', $page->body_style) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5" placeholder="from &lt;body style&gt;"></label>
    </div>
    <label class="block text-sm">SEO title <input name="meta_title" data-field="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <label class="block text-sm">SEO description <input name="meta_description" data-field="meta_description" value="{{ old('meta_description', $page->meta_description) }}" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <fieldset class="rounded-2xl border border-forest/15 p-4 space-y-3">
        <legend class="px-1 text-sm font-medium">Where this link appears</legend>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_in_header" value="1" @checked(old('show_in_header', $page->show_in_header))> Show in header</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_in_footer" value="1" @checked(old('show_in_footer', $page->show_in_footer ?? true))> Show in footer</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_page_title" value="1" data-field="show_page_title" @checked(old('show_page_title', $page->show_page_title ?? true))> Show page title above content</label>
    </fieldset>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $page->is_published))> Published</label>
    <button class="bg-forest text-cream rounded-full px-6 py-3">Save page</button>
</form>
<script>
    (() => {
        const root = document.querySelector('[data-url-style]');
        if (! root) return;
        const slug = root.closest('form').querySelector('[data-url-slug]');
        const preview = root.querySelector('[data-url-preview]');
        const paint = () => {
            const value = (slug?.value || 'your-page').replace(/^\/+|\/+$/g, '') || 'your-page';
            const style = root.querySelector('input[name="url_style"]:checked')?.value || 'prefixed';
            if (preview) preview.textContent = style === 'flat' ? '/' + value : '/p/' + value;
        };
        root.addEventListener('change', paint);
        slug?.addEventListener('input', paint);
        paint();
    })();
</script>
@endsection
