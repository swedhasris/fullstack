@extends('layouts.app')
@section('title', 'Add User')
@section('page-title', 'Add User')
@section('page-subtitle', 'User Management')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="bg-sn-card rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-white/5">
            <h2 class="text-base font-bold">Create New User</h2>
        </div>
        <form method="POST" action="{{ route('users.store') }}" class="p-5 space-y-4">
            @csrf
            @include('users._form', ['user' => null])
            <div class="flex justify-between pt-4 border-t border-white/5">
                <a href="{{ route('users.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Cancel
                </a>
                <button type="submit" class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-primary/90 transition-colors">
                    <i data-lucide="user-plus" class="w-4 h-4"></i> Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
