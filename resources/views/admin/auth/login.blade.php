<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login · {{ $siteName }}</title>
    @fonts
    @vite(['resources/css/app.css'])
</head>
<body class="pattern-bg min-h-screen grid place-items-center p-6">
    <form method="POST" action="{{ route('admin.login.store') }}" class="w-full max-w-md bg-cream rounded-3xl p-8 space-y-4">
        @csrf
        <p class="font-display text-4xl text-forest">Welcome back</p>
        <p class="text-forest/60">Sign in to manage {{ $siteName }}.</p>
        @error('email') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        <input name="email" type="email" value="{{ old('email') }}" required placeholder="Email" class="w-full rounded-2xl border border-forest/15 px-4 py-3">
        <input name="password" type="password" required placeholder="Password" class="w-full rounded-2xl border border-forest/15 px-4 py-3">
        <label class="flex items-center gap-2 text-sm text-forest/70"><input type="checkbox" name="remember"> Remember me</label>
        <button class="w-full bg-forest text-cream rounded-full py-3 font-semibold">Sign in</button>
    </form>
</body>
</html>
