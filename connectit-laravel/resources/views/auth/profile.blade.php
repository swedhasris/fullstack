@extends('layouts.app')
@section('title', 'Profile')
@section('page-title', 'My Profile')
@section('page-subtitle', 'Account Settings')

@section('content')
<div class="p-6 max-w-2xl mx-auto space-y-4">

    <div class="bg-sn-card rounded-2xl p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-2xl bg-primary/20 flex items-center justify-center text-primary font-black text-2xl">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-lg font-bold">{{ $user->name }}</h2>
                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                <span class="mt-1 inline-block text-[9px] font-black px-2 py-0.5 rounded {{ $user->role->color() }}">{{ $user->role->label() }}</span>
            </div>
        </div>

        @if($errors->any())
        <div class="bg-red-900/30 border border-red-500/30 text-red-300 rounded-xl px-4 py-3 text-sm mb-4">
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                           placeholder="+91 98765 43210">
                </div>
                <div>
                    <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Department</label>
                    <input type="text" name="department" value="{{ old('department', $user->department) }}"
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                           placeholder="IT, HR, Finance...">
                </div>
            </div>

            <div class="pt-4 border-t border-white/5">
                <h3 class="text-sm font-bold mb-3">Change Password</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Current Password</label>
                        <input type="password" name="current_password"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                               placeholder="Current password">
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">New Password</label>
                        <input type="password" name="password" minlength="8"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                               placeholder="Min. 8 characters">
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-gray-500 uppercase tracking-widest mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation"
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary/50 transition-all placeholder-gray-600"
                               placeholder="Repeat new password">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-primary/90 transition-colors">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Account Info --}}
    <div class="bg-sn-card rounded-2xl p-5">
        <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Account Information</h3>
        <dl class="space-y-2">
            @foreach([
                ['User ID',    $user->uid],
                ['Email',      $user->email],
                ['Role',       $user->role->label()],
                ['Department', $user->department ?? '—'],
                ['Last Login', $user->last_login?->format('M d, Y H:i') ?? 'Never'],
                ['Member Since', $user->created_at->format('M d, Y')],
            ] as [$label, $value])
            <div class="flex justify-between gap-3">
                <dt class="text-[9px] font-black text-gray-600 uppercase tracking-widest">{{ $label }}</dt>
                <dd class="text-xs text-gray-300 font-mono">{{ $value }}</dd>
            </div>
            @endforeach
        </dl>
    </div>
</div>
@endsection
