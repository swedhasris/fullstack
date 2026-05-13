@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'User Management')
@section('page-subtitle', 'Roles & Permissions')

@section('content')
<div class="p-6 space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs text-gray-500">{{ $users->total() }} total users</p>
        </div>
        @if(auth()->user()->canManageUsers())
        <a href="{{ route('users.create') }}" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Add User
        </a>
        @endif
    </div>

    {{-- Filters --}}
    <div class="bg-sn-card rounded-2xl p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Search</label>
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-500"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, department..."
                           class="w-full bg-white/5 border border-white/10 rounded-xl pl-9 pr-4 py-2 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600">
                </div>
            </div>
            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Role</label>
                <select name="role" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-1.5">Status</label>
                <select name="status" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm outline-none focus:border-primary/50 transition-all">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-primary/90 transition-colors">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter
            </button>
            <a href="{{ route('users.index') }}" class="flex items-center gap-2 bg-white/5 text-gray-400 px-4 py-2 rounded-xl text-xs font-bold hover:bg-white/10 transition-colors">
                <i data-lucide="x" class="w-3.5 h-3.5"></i> Clear
            </a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[9px] font-black uppercase text-gray-500 tracking-widest border-b border-white/5">
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Last Login</th>
                        @if(auth()->user()->canManageUsers())
                        <th class="px-5 py-3">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-white">{{ $user->name }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-1 rounded-lg text-[9px] font-black {{ $user->role->color() }}">
                                {{ $user->role->label() }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs text-gray-400">{{ $user->department ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="flex items-center gap-1.5 text-[10px] font-bold {{ $user->is_active ? 'text-green-400' : 'text-red-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-[10px] text-gray-500">{{ $user->last_login?->diffForHumans() ?? 'Never' }}</span>
                        </td>
                        @if(auth()->user()->canManageUsers())
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('users.edit', $user) }}" class="p-1.5 hover:bg-white/10 rounded-lg transition-colors" title="Edit">
                                    <i data-lucide="edit-2" class="w-3.5 h-3.5 text-gray-400"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.toggle-status', $user) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="p-1.5 hover:bg-white/10 rounded-lg transition-colors" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i data-lucide="{{ $user->is_active ? 'user-x' : 'user-check' }}" class="w-3.5 h-3.5 {{ $user->is_active ? 'text-red-400' : 'text-green-400' }}"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-gray-600 text-sm">No users found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-4 border-t border-white/5">{{ $users->links('pagination::tailwind') }}</div>
        @endif
    </div>
</div>
@endsection
