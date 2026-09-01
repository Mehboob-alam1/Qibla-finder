@extends('layouts.admin')
@section('title', $user->exists ? 'Edit user' : 'New user')
@section('heading', $user->exists ? 'Edit user' : 'New user')

@section('content')
<form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="stat-card rounded-3xl p-6 space-y-4 max-w-xl">
    @csrf
    @if ($user->exists) @method('PUT') @endif
    <label class="block text-sm">Name <input name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <label class="block text-sm">Email <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5"></label>
    <label class="block text-sm">Password <input type="password" name="password" class="mt-1 w-full rounded-2xl border border-forest/15 px-4 py-2.5" placeholder="{{ $user->exists ? 'Leave blank to keep' : '' }}"></label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $user->is_admin))> Administrator</label>
    <button class="bg-forest text-cream rounded-full px-6 py-3">Save user</button>
</form>
@endsection
