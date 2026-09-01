@extends('layouts.admin')
@section('title', 'Inbox')
@section('heading', 'Contact inbox')

@section('content')
<div class="stat-card rounded-3xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-sand"><tr><th class="p-3 text-start">From</th><th class="p-3">Subject</th><th class="p-3">When</th><th></th></tr></thead>
        <tbody>
            @foreach ($messages as $message)
                <tr class="border-t border-forest/10 {{ $message->isUnread() ? 'bg-gold/10' : '' }}">
                    <td class="p-3">{{ $message->name }}<div class="text-forest/50">{{ $message->email }}</div></td>
                    <td class="p-3">{{ $message->subject ?: '—' }}</td>
                    <td class="p-3">{{ $message->created_at->diffForHumans() }}</td>
                    <td class="p-3 text-end"><a class="text-gold" href="{{ route('admin.messages.show', $message) }}">Open</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $messages->links() }}</div>
@endsection
