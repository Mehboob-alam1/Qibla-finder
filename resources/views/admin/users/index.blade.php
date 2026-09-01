@extends('layouts.admin')
@section('title', 'Users')
@section('heading', 'Administrators')

@section('content')
<div class="flex justify-end mb-4">
    <a href="{{ route('admin.users.create') }}" class="bg-forest text-cream rounded-full px-5 py-2.5">New user</a>
</div>
<div class="stat-card rounded-3xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-sand"><tr><th class="p-3 text-start">Name</th><th class="p-3">Email</th><th class="p-3">Role</th><th></th></tr></thead>
        <tbody>
            @foreach ($users as $user)
                <tr class="border-t border-forest/10">
                    <td class="p-3">{{ $user->name }}</td>
                    <td class="p-3">{{ $user->email }}</td>
                    <td class="p-3">{{ $user->is_admin ? 'Admin' : 'User' }}</td>
                    <td class="p-3 text-end space-x-2">
                        <a class="text-gold" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                        <form class="inline" method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Delete this user?">
                            @csrf @method('DELETE')
                            <button class="text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
