@extends('layouts.admin')
@section('title', 'Guides')
@section('heading', 'Guides / blog')

@section('content')
<div class="flex justify-end mb-4">
    <a href="{{ route('admin.posts.create') }}" class="bg-forest text-cream rounded-full px-5 py-2.5">New article</a>
</div>
<div class="stat-card rounded-3xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-sand"><tr><th class="p-3 text-start">Title</th><th class="p-3">Locale</th><th class="p-3">Status</th><th></th></tr></thead>
        <tbody>
            @foreach ($posts as $post)
                <tr class="border-t border-forest/10">
                    <td class="p-3">{{ $post->title }}</td>
                    <td class="p-3">{{ $post->locale }}</td>
                    <td class="p-3">{{ $post->is_published ? 'Live' : 'Draft' }}</td>
                    <td class="p-3 text-end space-x-2">
                        <a class="text-gold" href="{{ route('admin.posts.edit', $post) }}">Edit</a>
                        <form class="inline" method="POST" action="{{ route('admin.posts.destroy', $post) }}" data-confirm="Delete this article?">
                            @csrf @method('DELETE')
                            <button class="text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $posts->links() }}</div>
@endsection
