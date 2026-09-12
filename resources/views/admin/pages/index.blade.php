@extends('layouts.admin')
@section('title', 'Pages')
@section('heading', 'CMS pages')

@section('content')
<div class="flex justify-end mb-4">
    <a href="{{ route('admin.pages.create') }}" class="bg-forest text-cream rounded-full px-5 py-2.5">New page</a>
</div>
<div class="stat-card rounded-3xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-sand text-start"><tr><th class="p-3 text-start">Title</th><th class="p-3">URL</th><th class="p-3">Locale</th><th class="p-3">Status</th><th></th></tr></thead>
        <tbody>
            @foreach ($pages as $page)
                <tr class="border-t border-forest/10">
                    <td class="p-3">{{ $page->title }}</td>
                    <td class="p-3"><a class="text-gold font-mono" href="{{ $page->publicPath() }}" target="_blank" rel="noopener">{{ $page->publicPath() }}</a></td>
                    <td class="p-3">{{ $page->locale }}</td>
                    <td class="p-3">{{ $page->is_published ? 'Live' : 'Draft' }}</td>
                    <td class="p-3 text-end space-x-2">
                        <a class="text-gold" href="{{ route('admin.pages.edit', $page) }}">Edit</a>
                        <form class="inline" method="POST" action="{{ route('admin.pages.destroy', $page) }}" data-confirm="Delete this page?">
                            @csrf @method('DELETE')
                            <button class="text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $pages->links() }}</div>
@endsection
