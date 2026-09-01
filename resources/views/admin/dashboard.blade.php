@extends('layouts.admin')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="grid sm:grid-cols-2 xl:grid-cols-5 gap-4">
    @foreach ([
        ['Views today', $stats['views_today']],
        ['Views (7d)', $stats['views_week']],
        ['Unread messages', $stats['messages']],
        ['Guides', $stats['posts']],
        ['Pages', $stats['pages']],
    ] as [$label, $value])
        <article class="stat-card rounded-3xl p-5">
            <p class="text-xs uppercase tracking-widest text-forest/50">{{ $label }}</p>
            <p class="font-display text-4xl mt-2">{{ $value }}</p>
        </article>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6 mt-8">
    <article class="stat-card rounded-3xl p-6">
        <h2 class="font-display text-3xl mb-4">Top paths (14 days)</h2>
        <ul class="space-y-2 text-sm">
            @forelse ($topPages as $row)
                <li class="flex justify-between"><span>{{ $row->path }}</span><span>{{ $row->total }}</span></li>
            @empty
                <li class="text-forest/50">No traffic recorded yet.</li>
            @endforelse
        </ul>
    </article>
    <article class="stat-card rounded-3xl p-6">
        <h2 class="font-display text-3xl mb-4">Latest messages</h2>
        <ul class="space-y-3 text-sm">
            @forelse ($latestMessages as $message)
                <li>
                    <a class="font-medium hover:text-gold" href="{{ route('admin.messages.show', $message) }}">{{ $message->name }}</a>
                    <p class="text-forest/60">{{ \Illuminate\Support\Str::limit($message->message, 80) }}</p>
                </li>
            @empty
                <li class="text-forest/50">Inbox is empty.</li>
            @endforelse
        </ul>
    </article>
</div>
@endsection
