@extends('layouts.admin')
@section('title', 'Message')
@section('heading', 'Message')

@section('content')
<article class="stat-card rounded-3xl p-6 max-w-3xl space-y-3">
    <p class="font-semibold">{{ $message->name }} · {{ $message->email }}</p>
    <p class="text-forest/50">{{ $message->created_at->toDayDateTimeString() }}</p>
    <p class="font-medium">{{ $message->subject }}</p>
    <p class="whitespace-pre-wrap leading-relaxed">{{ $message->message }}</p>
    <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" data-confirm="Delete this message?">
        @csrf @method('DELETE')
        <button class="text-red-700">Delete</button>
    </form>
</article>
@endsection
