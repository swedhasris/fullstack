@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-subtitle', 'User Management')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5">
            <h2 class="text-base font-bold">Edit: {{ $user->name }}</h2>
        </div>
        <form method="POST" action="{{ route('users.update', $user) }}" class="p-5 space-y-4">
            @csrf @method('PUT')
            @include('users._form', ['user' => $user])
            <div class="flex justify-between pt-4 border-t border-white/5">
                <a href="{{ route('users.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Cancel
                </a>
                <button type="submit" class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-primary/90 transition-colors">
                    <i data-lucide="save" class="w-4 h-4"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
