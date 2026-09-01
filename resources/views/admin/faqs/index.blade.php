@extends('layouts.admin')
@section('title', 'FAQs')
@section('heading', 'FAQs')

@section('content')
<div class="flex justify-end mb-4">
    <a href="{{ route('admin.faqs.create') }}" class="bg-forest text-cream rounded-full px-5 py-2.5">New FAQ</a>
</div>
<div class="stat-card rounded-3xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-sand"><tr><th class="p-3 text-start">Question</th><th class="p-3">Category</th><th class="p-3">Locale</th><th></th></tr></thead>
        <tbody>
            @foreach ($faqs as $faq)
                <tr class="border-t border-forest/10">
                    <td class="p-3">{{ $faq->question }}</td>
                    <td class="p-3">{{ $faq->category }}</td>
                    <td class="p-3">{{ $faq->locale }}</td>
                    <td class="p-3 text-end space-x-2">
                        <a class="text-gold" href="{{ route('admin.faqs.edit', $faq) }}">Edit</a>
                        <form class="inline" method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" data-confirm="Delete this FAQ?">
                            @csrf @method('DELETE')
                            <button class="text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $faqs->links() }}</div>
@endsection
