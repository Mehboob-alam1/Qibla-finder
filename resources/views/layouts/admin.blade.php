<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · {{ $siteName }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="admin-shell text-ink antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        <aside class="bg-ink text-cream px-5 py-6">
            <a href="{{ route('admin.dashboard') }}" class="font-display text-3xl text-gold block">{{ $siteName }}</a>
            <p class="text-xs uppercase tracking-widest text-cream/40 mt-1">Admin</p>
            <nav class="mt-8 space-y-1 text-sm">
                @foreach ([
                    ['admin.dashboard', 'Dashboard', 'admin.dashboard'],
                    ['admin.settings.edit', 'Settings', 'admin.settings.*'],
                    ['admin.pages.index', 'Pages', 'admin.pages.*'],
                    ['admin.posts.index', 'Guides', 'admin.posts.*'],
                    ['admin.faqs.index', 'FAQs', 'admin.faqs.*'],
                    ['admin.messages.index', 'Inbox', 'admin.messages.*'],
                    ['admin.users.index', 'Users', 'admin.users.*'],
                ] as [$route, $label, $pattern])
                    <a href="{{ route($route) }}" class="block rounded-xl px-3 py-2 {{ request()->routeIs($pattern) ? 'bg-gold text-ink' : 'hover:bg-white/10' }}">{{ $label }}</a>
                @endforeach
            </nav>
            <div class="mt-10 space-y-3 text-sm">
                <a href="{{ route('home') }}" class="block text-gold" target="_blank">View website</a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="text-cream/60 hover:text-cream">Sign out</button>
                </form>
            </div>
        </aside>
        <div>
            <header class="h-16 px-6 flex items-center justify-between border-b border-forest/10 bg-cream/80">
                <p class="font-medium">@yield('heading', 'Dashboard')</p>
                <p class="text-sm text-forest/60">{{ auth()->user()->name ?? '' }}</p>
            </header>
            <div class="p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-2xl bg-moss/15 text-forest px-4 py-3">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-2xl bg-red-50 text-red-800 px-4 py-3">{{ $errors->first() }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
