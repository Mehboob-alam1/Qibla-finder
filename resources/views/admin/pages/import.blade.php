@extends('layouts.admin')
@section('title', 'Import HTML page')
@section('heading', 'Import HTML page')

@section('content')
<form method="POST" action="{{ route('admin.pages.import.store') }}" enctype="multipart/form-data" class="stat-card rounded-3xl p-6 space-y-5 max-w-2xl">
    @csrf
    <p class="text-sm text-forest/70">Upload an <code>index.html</code> (or any <code>.html</code> file). The page is created <strong>published</strong> and you are taken straight to the live URL.</p>

    <label class="block text-sm font-medium">HTML file
        <input type="file" name="file" accept=".html,.htm,text/html" required class="mt-2 block w-full text-sm">
    </label>
    @error('file') <p class="text-sm text-red-700">{{ $message }}</p> @enderror

    <div class="grid md:grid-cols-2 gap-4">
        <label class="text-sm">Locale
            <select name="locale" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
                @foreach ($locales as $code)
                    <option value="{{ $code }}" @selected(old('locale', 'en') === $code)>{{ $code }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm">URL style
            <select name="url_style" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
                <option value="prefixed" @selected(old('url_style', 'prefixed') === 'prefixed')>/p/your-slug</option>
                <option value="flat" @selected(old('url_style') === 'flat')>/your-slug</option>
            </select>
        </label>
    </div>

    <label class="block text-sm">Slug override <span class="text-forest/50 font-normal">(optional — auto from title)</span>
        <input name="slug" value="{{ old('slug') }}" placeholder="leave blank to auto-generate" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5">
    </label>
    @error('slug') <p class="text-sm text-red-700">{{ $message }}</p> @enderror

    <div class="space-y-2">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_in_footer" value="1" @checked(old('show_in_footer', true))> Show in footer</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="show_in_header" value="1" @checked(old('show_in_header'))> Show in header</label>
    </div>

    <div class="flex flex-wrap gap-3 pt-2">
        <button class="bg-forest text-cream rounded-full px-6 py-3 font-semibold">Import &amp; publish live</button>
        <a href="{{ route('admin.pages.index') }}" class="rounded-full border border-forest/20 px-6 py-3 text-sm">Cancel</a>
    </div>
</form>
@endsection
